<?php

namespace Drupal\lightning\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\Shared\CommandTrait;
use Drupal\Console\Command\Shared\ModuleTrait;
use Drupal\Console\Command\Shared\FormTrait;
use Drupal\Console\Command\Shared\ConfirmationTrait;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Extension\Manager;
use Drupal\Console\Core\Utils\FileQueue;
use Drupal\Console\Command\Generate\ProfileCommand;
use Drupal\lightning\Generator\SubProfileGenerator;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\Console\Core\Utils\TwigRenderer;
use Drupal\Console\Core\Utils\TranslatorManager;
use Drupal\Console\Utils\Validator;
use Drupal\Console\Utils\Site;
use GuzzleHttp\Client;


class SubProfileCommand extends ProfileCommand {

  use CommandTrait;
  use ModuleTrait;
  use FormTrait;
  use ConfirmationTrait;

  /**
   * @var string \Drupal::root().
   */
  protected $appRoot;

  /**
   * @var array.
   *
   * A list of all Lightning modules.
   */
  private $lightningComponents;

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('lightning:subprofile')
      ->setDescription($this->trans('Generate a subprofile of Lightning'))
      ->setHelp($this->trans('The <info>lightning:subprofile</info> command helps you generate a new subprofile of Lightning'))
      ->addOption(
        'profile',
        '',
        InputOption::VALUE_REQUIRED,
        $this->trans('The name of the subprofile')
      )
      ->addOption(
        'machine-name',
        '',
        InputOption::VALUE_REQUIRED,
        $this->trans('The machine name of the subprofile (lowercase and underscore only)')
      )
      ->addOption(
        'description',
        '',
        InputOption::VALUE_OPTIONAL,
        $this->trans('Subprofile description')
      )
      ->addOption(
        'core',
        '',
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.generate.profile.options.core'),
        '8.x'
      )
      ->addOption(
        'dependencies',
        false,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.generate.profile.options.dependencies')
      )
      ->addOption(
        'excluded_dependencies',
        false,
        InputOption::VALUE_OPTIONAL,
        $this->trans('Dependencies of Lightning to exclude separated by commas (i.e. lightning_media, lightning_landing_page')
      )
      ->addOption(
        'distribution',
        false,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.generate.profile.options.distribution'),
        false
      );
  }

  /**
   * ProfileCommand constructor.
   */
  public function __construct() {
    $generator = new SubProfileGenerator();
    $stringConverter = new StringConverter();
    $httpClient = new Client();
    $appRoot = \Drupal::root();
    $site = new Site($appRoot);
    $extensionManager = new Manager($site, $appRoot);
    $validator = new Validator($extensionManager);
    $translater = new TranslatorManager();
    $renderer = new TwigRenderer($translater, $stringConverter);
    $renderer->setSkeletonDirs([__DIR__ . '/../../templates/']);
    $fileQueue = new FileQueue();
    $generator->setRenderer($renderer);
    $generator->setFileQueue($fileQueue);
    $this->lightningComponents = [
      'lightning_core',
      'lightning_contact_form',
      'lightning_page',
      'lightning_search',
      'lightning_layout',
      'lightning_landing_page',
      'lightning_media',
      'lightning_landing_page',
      'lightning_media_document',
      'lightning_media_image',
      'lightning_media_instagram',
      'lightning_media_twitter',
      'lightning_media_video',
      'lightning_workflow',
      'lightning_preview',
    ];
    parent::__construct($extensionManager, $generator, $stringConverter, $validator, $appRoot, $site, $httpClient);
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);
    $validators = $this->validator;

    try {
      // A profile is technically also a module, so we can use the same
      // validator to check the name.
      $profile = $input->getOption('profile') ? $validators->validateModuleName($input->getOption('profile')) : null;
    } catch (\Exception $error) {
      $io->error($error->getMessage());

      return;
    }

    if (!$profile) {
      $profile = $io->ask(
        $this->trans('The name of the subprofile'),
        '',
        function ($profile) use ($validators) {
          return $validators->validateModuleName($profile);
        }
      );
      $input->setOption('profile', $profile);
    }

    try {
      $machine_name = $input->getOption('machine-name') ? $validators->validateModuleName($input->getOption('machine-name')) : null;
    } catch (\Exception $error) {
      $io->error($error->getMessage());

      return;
    }

    if (!$machine_name) {
      $machine_name = $io->ask(
        $this->trans('commands.generate.profile.questions.machine-name'),
        $this->stringConverter->createMachineName($profile),
        function ($machine_name) use ($validators) {
          return $validators->validateMachineName($machine_name);
        }
      );
      $input->setOption('machine-name', $machine_name);
    }

    $description = $input->getOption('description');
    if (!$description) {
      $description = $io->ask(
        $this->trans('Subprofile description'),
        'My Lightning Sub-Profile'
      );
      $input->setOption('description', $description);
    }

    $dependencies = $input->getOption('dependencies');
    if (!$dependencies) {
      if ($io->confirm(
        $this->trans('commands.generate.profile.questions.dependencies'),
        true
      )
      ) {
        $dependencies = $io->ask(
          $this->trans('commands.generate.profile.options.dependencies'),
          ''
        );
      }
      $input->setOption('dependencies', $dependencies);
    }

    $excluded_dependencies = $input->getOption('excluded_dependencies');
    if (!$excluded_dependencies) {
      if ($io->confirm($this->trans('Would you like to exclude any of Lightning\'s Components from being installed'), false)) {
        if ($io->confirm($this->trans('Would you like to see a list of Components that can be excluded?'), false)) {
          $io->write(implode(', ', $this->lightningComponents));
        }
        $excluded_dependencies = $io->ask($this->trans('Dependencies of Lightning to exclude separated by commas (i.e. lightning_media, lightning_landing_page'), '');
      }
      $input->setOption('excluded_dependencies', $excluded_dependencies);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    if (!$this->confirmGeneration($io)) {
      return;
    }

    $profile = $this->validator->validateModuleName($input->getOption('profile'));
    $machine_name = $this->validator->validateMachineName($input->getOption('machine-name'));
    $description = $input->getOption('description');
    $core = $input->getOption('core');
    $distribution = $input->getOption('distribution');
    $profile_path = $this->appRoot . '/profiles/custom';

    // Check if all module dependencies are available.
    $dependencies = $this->validator->validateModuleDependencies($input->getOption('dependencies'));
    if ($dependencies) {
      // @todo ProfileCommand::checkDependencies is private so we aren't
      // actually checking to see if they are present.
      $dependencies = $dependencies['success'];
    }
    $excluded_dependencies = $this->validateExcludedDependencies($input->getOption('excluded_dependencies'));
    $this->generator->generate(
      $profile,
      $machine_name,
      $profile_path,
      $description,
      $core,
      $dependencies,
      $distribution,
      $excluded_dependencies
    );
  }

  /**
   * @param string $excluded_dependencies
   * @return mixed
   *
   * This just formats the list into an array.
   */
  protected function validateExcludedDependencies($excluded_dependencies) {
    if (empty($excluded_dependencies)) {
      // Need an explicit false here for twig.
      return FALSE;
    }
    $validated_excluded_dependencies = [];
    $excluded_dependencies = explode(',', $excluded_dependencies);
    foreach ($excluded_dependencies as $excluded_dependency) {
      if (in_array($excluded_dependency, $this->lightningComponents)) {
        $validated_excluded_dependencies[] = trim($excluded_dependency);
      }
      else {
        // @todo warn?
      }
    }
    return $validated_excluded_dependencies;
  }

}
