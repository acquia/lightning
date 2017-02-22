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
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\InfoParser;
use Drupal\lightning\Generator\SubProfileGenerator;
use Drupal\lightning_core\Element;
use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

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

  /**
   * ProfileCommand constructor.
   */
  public function __construct() {
    $translater = new TranslatorManager();
    $stringConverter = new StringConverter();
    $renderer = new TwigRenderer($translater, $stringConverter);
    $renderer->setSkeletonDirs([__DIR__ . '/../../templates/']);
    $fileQueue = new FileQueue();
    $generator = new SubProfileGenerator();
    $generator->setRenderer($renderer);
    $generator->setFileQueue($fileQueue);
    $httpClient = new Client();
    $appRoot = \Drupal::root();
    $configurationManager = new ConfigurationManager();
    $site = new Site($appRoot, $configurationManager);
    $extensionManager = new Manager($site, $appRoot);
    $validator = new Validator($extensionManager);
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
      $description = $io->ask($this->trans('Subprofile description'), '');
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
      if ($io->confirm($this->trans('Would you like to exclude any of Lightning\'s top-level components from the installation? (Experimental components are excluded automatically.)'), false)) {
        $excludable_components = self::getTopLevelComponents(['lightning_core', 'lightning_preview']);
        $question = new ChoiceQuestion('List the top-level components you would like to exclude, separated by commas.', $excludable_components);
        $question->setMultiselect(true);
        $io->writeln('The following components can be excluded: ' . Element::oxford($question->getChoices()));
        $excluded_dependencies = $io->askChoiceQuestion($question);
      }
      $input->setOption('excluded_dependencies', $excluded_dependencies);
    }

    $excluded_subcomponents = $input->getOption('excluded_subcomponents');
    if (!$excluded_subcomponents) {
      if ($io->confirm($this->trans('Would you like to exclude any of Lightning\'s sub-components?'), false)) {
        // Subcomponents whose parent is already excluded are automatically
        // excluded. So we build a list of excludable subcomponents whose parent
        // component isn't already excluded.
        $excludable_subcomponents = [];
        $top_level_components = self::getTopLevelComponents();
        $non_excluded_tlcs = array_diff($top_level_components, $excluded_dependencies);
        foreach ($non_excluded_tlcs as $non_excluded_tlc) {
          $tlc_subcomponents = self::getSubComponents($non_excluded_tlc);
          $excludable_subcomponents = array_merge($excludable_subcomponents, $tlc_subcomponents);
        }

        $question = new ChoiceQuestion('List the sub-components you would like to exclude, separated by commas.', $excludable_subcomponents);
        $question->setMultiselect(true);
        $io->writeln('The following sub-components can be excluded: ' . Element::oxford($question->getChoices()));
        $excluded_subcomponents = $io->askChoiceQuestion($question);
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
   * @param array $excluded_dependencies
   *   Dependencies to exclude.
   *
   * Adds the provided component(s) to the excluded list and, if the provided
   * component is a top-level component, all of its subcomponents too.
   */
  protected function buildExcludedDependenciesList($excluded_dependencies) {
    $excluded_dependencies_list = [];
    foreach ($excluded_dependencies as $excluded_dependency) {
      if (array_key_exists($excluded_dependency, $this->getLightningComponents())) {
        $excluded_dependencies_list[] = $excluded_dependency;
        if (in_array($excluded_dependency, $this->getTopLevelComponents())) {
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

  public static function getLightningComponents() {
    $appRoot = \Drupal::root();
    $extensions = new ExtensionDiscovery($appRoot);
    $extensions = $extensions->scan('module');

    $lightning_extensions = self::array_filter_key($extensions, function($key) {
      return strpos($key, 'lightning_') === 0;
    });

    $lightning_components = [];
    foreach($lightning_extensions as $machine_name => $lightning_extension) {
      $InfoParser = new InfoParser();
      $info = $InfoParser->parse($lightning_extension->getPathname());
      $lightning_components[$machine_name] = [
        'name' => $info['name'],
      ];
      if (isset($info['components'])) {
        $lightning_components[$machine_name]['subcomponents'] =  $info['components'];
      }
    }

    return $lightning_components;
  }

  /**
   * @param $input
   * @param $callback
   * @return array|null
   *
   * Filter an array by keys.
   */
  private static function array_filter_key(array $input, $callback) {
    $keys = array_keys($input);
    $filteredKeys = array_filter($keys, $callback);
    if (empty($filteredKeys)) {
      return [];
    }
    $input = array_intersect_key($input, array_flip($filteredKeys));

    return $input;
  }

  /**
   * @param array $excludedComponents
   *   An array of top-level components to exclude.
   * @return array of top-level Lightning components.
   */
  public static function getTopLevelComponents($excludedComponents = []) {
    $topLevelComponents = [];
    foreach (self::getLightningComponents() as $component => $attributes) {
      if (isset($attributes['subcomponents'])) {
        $topLevelComponents[] = $component;
      }
    }
    return array_diff($topLevelComponents, $excludedComponents);
  }

  /**
   * @param string $topLevelComponent
   * @return array of subcomponents of the provided top-level component.
   *
   * @throws \Exception
   */
  public static function getSubComponents($topLevelComponent) {
    $topLevelComponents = self::getTopLevelComponents();
    if (!in_array($topLevelComponent, $topLevelComponents)) {
      throw new \Exception($topLevelComponent . ' is Not a top-level component.');
    }
    $components = self::getLightningComponents();
    return $components[$topLevelComponent]['subcomponents'];
  }

}
