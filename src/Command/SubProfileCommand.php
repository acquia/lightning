<?php

namespace Drupal\lightning\Command;

use Drupal\Console\Command\Generate\ProfileCommand;
use Drupal\Console\Command\Shared\FormTrait;
use Drupal\Console\Command\Shared\ModuleTrait;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Core\Utils\ConfigurationManager;
use Drupal\Console\Core\Utils\FileQueue;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\Console\Core\Utils\TwigRenderer;
use Drupal\Console\Core\Utils\TranslatorManager;
use Drupal\Console\Extension\Manager as ExtensionManager;
use Drupal\Console\Utils\Site;
use Drupal\Console\Utils\Validator;
use Drupal\Core\Extension\InfoParser;
use Drupal\lightning\ComponentInfo;
use Drupal\lightning\Generator\SubProfileGenerator;
use Drupal\lightning_core\Element;
use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * A Drupal Console command to generate a Lightning sub-profile.
 */
class SubProfileCommand extends ProfileCommand {

  use ModuleTrait;
  use FormTrait;

  /**
   * The modules to exclude from the sub-profile.
   *
   * @var string[]
   */
  protected $excludedDependencies = [];

  /**
   * The canonical list of modules to exclude.
   *
   * @var string[]
   */
  protected $exclude = [];

  protected $componentInfo;

  /**
   * ProfileCommand constructor.
   */
  public function __construct() {
    $appRoot = \Drupal::root();
    $site = new Site($appRoot, new ConfigurationManager());
    $extensionManager = new ExtensionManager($site, $appRoot);

    parent::__construct(
      $extensionManager,
      new SubProfileGenerator(),
      new StringConverter(),
      new Validator($extensionManager),
      $appRoot,
      $site,
      new Client()
    );

    $this->componentInfo = new ComponentInfo($appRoot, new InfoParser());

    $this->translator = new TranslatorManager();
    $this->translator
      ->loadResource('en', __DIR__ . '/../../lightning-en/translations');

    $renderer = new TwigRenderer($this->translator, $this->stringConverter);
    $renderer->setSkeletonDirs([__DIR__ . '/../../templates/']);

    $this->generator->setRenderer($renderer);
    $this->generator->setFileQueue(new FileQueue());
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('lightning:subprofile')
      ->setDescription($this->trans('Generate a subprofile of Lightning'))
      ->setHelp($this->trans('The <info>lightning:subprofile</info> command helps you generate a new subprofile of Lightning'))
      ->addOption(
        'name',
        '',
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.lightning.subprofile.options.name')
      )
      ->addOption(
        'machine-name',
        '',
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.lightning.subprofile.options.machine_name')
      )
      ->addOption(
        'description',
        '',
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.lightning.subprofile.options.description')
      )
      ->addOption(
        'dependencies',
        FALSE,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.generate.profile.options.dependencies')
      )
      ->addOption(
        'exclude',
        NULL,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.lightning.subprofile.options.exclude')
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    $profile = $input->getOption('name');
    if ($profile) {
      $this->validator->validateMachineName($profile);
    }
    else {
      $profile = $io->ask(
        $this->trans('commands.lightning.subprofile.description'),
        '',
        [$this->validator, 'validateModuleName']
      );
      $input->setOption('name', $profile);
    }

    $machine_name = $input->getOption('machine-name');
    if ($machine_name) {
      $this->validator->validateModuleName($machine_name);
    }
    else {
      $machine_name = $io->ask(
        $this->trans('commands.generate.profile.questions.machine-name'),
        $this->stringConverter->createMachineName($profile),
        [$this->validator, 'validateMachineName']
      );
      $input->setOption('machine-name', $machine_name);
    }

    $description = $input->getOption('description');
    if (!$description) {
      $description = $io->ask($this->trans('commands.lightning.subprofile.questions.description'), '');
      $input->setOption('description', $description);
    }

    $dependencies = $input->getOption('dependencies');
    if (!$dependencies) {
      if ($io->confirm($this->trans('commands.generate.profile.questions.dependencies'), TRUE)) {
        $dependencies = $io->ask($this->trans('commands.generate.profile.options.dependencies'), '');
      }
      $input->setOption('dependencies', $dependencies);
    }

    $components = array_diff_key($this->getMainComponentInfo(), array_flip($this->exclude));

    foreach ($components as $component) {
      $this->requestComponent($component, $io);
    }
  }

  /**
   * Collects information about including a Lightning component.
   *
   * @param array $component
   *   The parsed component info.
   * @param \Drupal\Console\Core\Style\DrupalStyle $io
   *   The I/O handler.
   */
  protected function requestComponent(array $component, DrupalStyle $io) {
    // We always include Lightning Core, or it won't be much of a Lightning
    // sub-profile.
    if ($component['name'] == 'lightning_core') {
      $include = TRUE;
    }
    else {
      $question = sprintf(
        $this->trans('commands.lightning.subprofile.questions.include-component'),
        $component['name']
      );
      if (isset($component['experimental'])) {
        $question .= sprintf(
          ' <fg=yellow>%s</>',
          $this->trans('commands.lightning.subprofile.experimental')
        );
      }
      $include = $io->confirm($question, empty($component['experimental']));
    }

    if ($include && !empty($component['components'])) {
      $sub_components = $component['components'];
      sort($sub_components);

      $question = sprintf(
        $this->trans('commands.lightning.subprofile.questions.exclude-components'),
        $component['name'],
        count($sub_components),
        Element::oxford($sub_components)
      );

      if ($io->confirm($question, FALSE)) {
        $question = new ChoiceQuestion(
          $this->trans('commands.lightning.subprofile.questions.choose-excluded-components'),
          $sub_components
        );
        $question->setMultiselect(TRUE);

        $this->exclude = array_merge($this->exclude, $io->askChoiceQuestion($question));
      }
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

    $profile = $this->validator->validateModuleName($input->getOption('name'));
    $machine_name = $this->validator->validateMachineName($input->getOption('machine-name'));
    $description = $input->getOption('description');
    $profile_path = $this->appRoot . '/profiles/custom';

    // Check if all module dependencies are available.
    $dependencies = $this->validator->validateModuleDependencies($input->getOption('dependencies'));
    if ($dependencies) {
      // @todo ProfileCommand::checkDependencies is private so we aren't
      // actually checking to see if they are present.
      $dependencies = $dependencies['success'];
    }
    $this->buildExcludedDependenciesList($input->getOption('exclude-dependencies'));
    $this->buildExcludedDependenciesList($input->getOption('exclude-subcomponents'));
    $this->generator->generate(
      $profile,
      $machine_name,
      $profile_path,
      $description,
      $dependencies,
      $this->excludedDependencies,
      NULL
    );
  }

  /**
   * Excludes the given components (and all applicable sub-components).
   *
   * @param string[] $excluded_dependencies
   *   Dependencies to exclude.
   */
  protected function buildExcludedDependenciesList(array $excluded_dependencies) {
    $excluded_dependencies_list = [];
    foreach ($excluded_dependencies as $excluded_dependency) {
      if (array_key_exists($excluded_dependency, $this->getComponentInfo())) {
        $excluded_dependencies_list[] = $excluded_dependency;
        if (in_array($excluded_dependency, $this->getMainComponentInfo())) {
          // If its a top-level-component, add its subcomponents too.
          $subcomponents = $this->listSubComponents(trim($excluded_dependency));
          $excluded_dependencies_list = array_merge($excluded_dependencies_list, $subcomponents);
        }
      }
    }
    $this->excludedDependencies = array_merge($this->excludedDependencies, $excluded_dependencies_list);
  }

  /**
   * Returns info for all Lightning components.
   *
   * @return array[]
   *   Parsed info for all Lightning components, including sub-components.
   */
  protected function getComponentInfo() {
    $not_hidden = function (array $info) {
      return empty($info['hidden']);
    };

    $component_info = array_filter($this->componentInfo->getAll(), $not_hidden);

    foreach ($component_info as $component => $info) {
      foreach ((array) @$info['components'] as $child) {
        $component_info[$child]['parent'] = $component;
      }
    }

    return $component_info;
  }

  /**
   * Returns info for top-level components.
   *
   * @param string[] $exclude
   *   The top-level components to exclude.
   *
   * @return array[]
   *   Parsed info for all top-level Lightning components.
   */
  protected function getMainComponentInfo(array $exclude = []) {
    $main_components = array_filter($this->getComponentInfo(), function (array $info) {
      return empty($info['parent']);
    });

    return array_diff_key($main_components, $exclude);
  }

  /**
   * Lists sub-components of a top-level component.
   *
   * @param string $parent
   *   The parent (top-level) component.
   *
   * @return string[]
   *   The sub-components of the provided top-level component.
   */
  protected function listSubComponents($parent) {
    $components = $this->getComponentInfo();

    if (isset($components[$parent])) {
      return (array) $components[$parent]['components'];
    }
    else {
      return [];
    }
  }

}
