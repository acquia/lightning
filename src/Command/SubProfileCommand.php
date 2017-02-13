<?php

namespace Drupal\lightning\Command;

use Drupal\Console\Command\Generate\ProfileCommand;
use Drupal\Console\Command\Shared\ConfirmationTrait;
use Drupal\Console\Command\Shared\FormTrait;
use Drupal\Console\Command\Shared\ModuleTrait;
use Drupal\Console\Core\Command\Shared\CommandTrait;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Core\Utils\ConfigurationManager;
use Drupal\Console\Core\Utils\FileQueue;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\Console\Core\Utils\TwigRenderer;
use Drupal\Console\Core\Utils\TranslatorManager;
use Drupal\Console\Extension\Manager;
use Drupal\Console\Utils\Site;
use Drupal\Console\Utils\Validator;
use Drupal\lightning\Generator\SubProfileGenerator;
use Drupal\lightning_core\Element;
use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
   * @var array
   */
  protected $topLevelComponents;

  /**
   * @var array
   */
  protected $excludedDependencies;

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
        $this->trans('Top-level components of Lightning to exclude separated by commas.')
      )
      ->addOption(
        'excluded_subcomponents',
        false,
        InputOption::VALUE_OPTIONAL,
        $this->trans('Lightning sub-components to exclude separated by commas.')
      )
      ->addOption(
        'distribution',
        false,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.generate.profile.options.distribution'),
        false
      );
  }

  public static function getLightningComponents() {
    return [
      'lightning_core' => [
        'Lightning Core',
        'parent' => null
      ],
      'lightning_contact_form' => [
        'Lightning Contact Form',
        'parent' => 'lightning_core'
      ],
      'lightning_page' => [
        'Lightning Page',
        'parent' => 'lightning_core'
      ],
      'lightning_search' => [
        'Lightning Search',
        'parent' => 'lightning_core'
      ],
      'lightning_layout' => [
        'Lightning Layout',
        'parent' => null
      ],
      'lightning_landing_page' => [
        'Lightning Landing Page',
        'parent' => 'lightning_layout'
      ],
      'lightning_media' => [
        'Lightning Media',
        'parent' => null
      ],
      'lightning_media_document' => [
        'Lightning Media Document',
        'parent' => 'lightning_media'
      ],
      'lightning_media_image' => [
        'Lightning Media Image',
        'parent' => 'lightning_media'
      ],
      'lightning_media_instagram' => [
        'Lightning Media Instagram',
        'parent' => 'lightning_media'
      ],
      'lightning_media_twitter' => [
        'Lightning Media Twitter',
        'parent' => 'lightning_media'
      ],
      'lightning_media_video' => [
        'Lightning Media Video',
        'parent' => 'lightning_media'
      ],
      'lightning_workflow' => [
        'Lightning Workflow',
        'parent' => null
      ],
      'lightning_preview' => [
        'Lightning Preview',
        'parent' => null
      ]
    ];
  }

  /**
   * ProfileCommand constructor.
   */
  public function __construct() {
    $generator = new SubProfileGenerator();
    $stringConverter = new StringConverter();
    $httpClient = new Client();
    $appRoot = \Drupal::root();
    $configurationManager = new ConfigurationManager();
    $site = new Site($appRoot, $configurationManager);
    $extensionManager = new Manager($site, $appRoot);
    $validator = new Validator($extensionManager);
    $translater = new TranslatorManager();
    $renderer = new TwigRenderer($translater, $stringConverter);
    $renderer->setSkeletonDirs([__DIR__ . '/../../templates/']);
    $fileQueue = new FileQueue();
    $generator->setRenderer($renderer);
    $generator->setFileQueue($fileQueue);
    $this->setTopLevelComponents();
    $this->excludedDependencies = [];
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
      if ($io->confirm($this->trans('commands.generate.profile.questions.dependencies'), true)) {
        $dependencies = $io->ask($this->trans('commands.generate.profile.options.dependencies'), '');
      }
      $input->setOption('dependencies', $dependencies);
    }

    $excluded_dependencies = $input->getOption('excluded_dependencies');
    if (!$excluded_dependencies) {
      if ($io->confirm($this->trans('Would you like to exclude any of Lightning\'s top-level components from the installation?'), false)) {
        if ($io->confirm($this->trans('Would you like to see a list of top-level components that can be excluded?'), true)) {
          $io->writeln(Element::oxford($this->getTopLevelComponents(['lightning_core'])));
        }
        $excluded_dependencies = $io->ask($this->trans('Top-level components of Lightning to exclude separated by commas.'), '');
      }
      $input->setOption('excluded_dependencies', $excluded_dependencies);
    }

    $excluded_subcomponents = $input->getOption('excluded_subcomponents');
    if (!$excluded_subcomponents) {
      if ($io->confirm($this->trans('Would you like to exclude any of Lightning\'s sub-components?'), false)) {
        if ($io->confirm($this->trans('Would you like to see a list of sub-components that can be excluded? (Sub-components of excluded top-level components are automatically excluded.)'), true)) {
          $subcomponents = [];
          foreach ($this->getTopLevelComponents() as $tlc) {
            $foo = $this->getSubComponents($tlc);
            $subcomponents = array_merge($subcomponents, $foo);
          }
          $io->writeln(Element::oxford($subcomponents));
        }
        $excluded_subcomponents = $io->ask($this->trans('Lightning sub-components to exclude separated by commas.'), '');
      }
      $input->setOption('excluded_subcomponents', $excluded_subcomponents);
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
    $this->buildExcludedDependenciesList($input->getOption('excluded_dependencies'));
    $this->buildExcludedDependenciesList($input->getOption('excluded_subcomponents'));
    $this->generator->generate(
      $profile,
      $machine_name,
      $profile_path,
      $description,
      $core,
      $dependencies,
      $distribution,
      $this->excludedDependencies
    );
  }

  /**
   * @param string $excluded_dependencies
   * @return mixed
   *
   * Adds the provided component to the excluded list and, if the provided
   * component is a top-level component, all of its subcomponents too.
   */
  protected function buildExcludedDependenciesList($excluded_dependencies) {
    $excluded_dependencies_list = [];
    $excluded_dependencies = explode(',', $excluded_dependencies);
    foreach ($excluded_dependencies as $excluded_dependency) {
      if (array_key_exists(trim($excluded_dependency), $this->getLightningComponents())) {
        $excluded_dependencies_list[] = trim($excluded_dependency);
        if (in_array(trim($excluded_dependency), $this->getTopLevelComponents())) {
          // If its a top-level-component, add its subcomponents too.
          $subcomponents = $this->getSubComponents(trim($excluded_dependency));
          foreach ($subcomponents as $subcomponent) {
            $excluded_dependencies_list[] = $subcomponent;
          }
        }
      }
    }
    $this->excludedDependencies = array_merge($this->excludedDependencies, $excluded_dependencies_list);
  }

  /**
   *
   */
  protected function setTopLevelComponents() {
    $topLevelComponents = [];
    foreach ($this->getLightningComponents() as $component => $attributes) {
      if ($attributes['parent'] === null) {
        $topLevelComponents[] = $component;
      }
    }
    $this->topLevelComponents = $topLevelComponents;
  }

  /**
   * @param array $excludedComponents
   *   An array of top-level components to exclude.
   *
   * @return array
   */
  public function getTopLevelComponents($excludedComponents = []) {
    return array_diff($this->topLevelComponents, $excludedComponents);
  }

  /**
   * @param string $topLevelComponent
   * @return array
   */
  protected function getSubComponents($topLevelComponent) {
    $subcomponents = [];
    foreach ($this->getLightningComponents() as $component => $attributes) {
      if (isset($attributes['parent'])) {
        if ($attributes['parent'] == $topLevelComponent) {
          $subcomponents[] = $component;
        }
      }
    }
    return $subcomponents;
  }

}
