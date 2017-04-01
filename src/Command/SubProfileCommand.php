<?php

namespace Drupal\lightning\Command;

use Drupal\Console\Command\Shared\ConfirmationTrait;
use Drupal\Console\Core\Command\Shared\CommandTrait;
use Drupal\Console\Core\Generator\Generator;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\Console\Core\Utils\TwigRenderer;
use Drupal\Console\Utils\TranslatorManager;
use Drupal\Console\Utils\Validator;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\lightning\ComponentDiscovery;
use Drupal\lightning_core\Element;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * A Drupal Console command to generate a Lightning sub-profile.
 */
class SubProfileCommand extends Command {

  use CommandTrait;
  use ConfirmationTrait;

  /**
   * The Lightning component discovery helper.
   *
   * @var \Drupal\lightning\ComponentDiscovery
   */
  protected $componentDiscovery;

  /**
   * The info file parser.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * The profile generator.
   *
   * @var \Drupal\lightning\Generator\SubProfileGenerator
   */
  protected $generator;

  /**
   * The string converter.
   *
   * @var \Drupal\Console\Core\Utils\StringConverter
   */
  protected $stringConverter;

  /**
   * The validation service.
   *
   * @var \Drupal\Console\Utils\Validator
   */
  protected $validator;

  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * Modules to include in the sub-profile.
   *
   * @var string[]
   */
  protected $include = [];

  /**
   * Modules to exclude from the sub-profile.
   *
   * @var string[]
   */
  protected $exclude = [];

  /**
   * Cache of parsed info for Lightning's main components.
   *
   * @var array[]
   */
  protected $componentInfo;

  /**
   * SubProfileCommand constructor.
   *
   * @param \Drupal\Console\Core\Generator\Generator $profile_generator
   *   The profile generator.
   * @param \Drupal\Console\Core\Utils\StringConverter $string_converter
   *   The string converter.
   * @param \Drupal\Console\Utils\Validator $validator
   *   The validation service.
   * @param string $app_root
   *   The Drupal application root.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info file parser.
   * @param \Drupal\Console\Utils\TranslatorManager $translator
   *   (optional) The translator manager.
   */
  public function __construct(Generator $profile_generator, StringConverter $string_converter, Validator $validator, $app_root, InfoParserInterface $info_parser, TranslatorManager $translator = NULL) {
    parent::__construct('lightning:subprofile');

    $this->componentDiscovery = new ComponentDiscovery($app_root);
    $this->infoParser = $info_parser;

    // The SkeletonDirs in the existing TwigRenderer contain directories that
    // contain files with the same names as our templates. TwigRenderer
    // doesn't provide a method to remove those directories and as a result, the
    // other templates are always used. For now we can work around this by
    // creating a whole new TwigRenderer, but it would be nice if it just
    // provided a ::resetSkeletonDirs method.
    $renderer = new TwigRenderer($translator, $string_converter);
    $renderer->setSkeletonDirs([__DIR__ . '/../../templates/']);
    $profile_generator->setRenderer($renderer);
    $this->generator = $profile_generator;

    $this->stringConverter = $string_converter;
    $this->validator = $validator;
    $this->appRoot = $app_root;

    // For reasons I can't yet figure out, adding the DrupalCommand annotation
    // to this class, which would allow translations to be loaded automatically,
    // causes the command to be unrecognized by Drupal Console. Which is
    // disturbing...but we can work around it here.
    if ($translator) {
      $translator->addResourceTranslationsByExtension('lightning', 'module');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setDescription($this->trans('commands.lightning.subprofile.description'))
      ->addOption(
        'name',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.profile.options.profile')
      )
      ->addOption(
        'machine-name',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.profile.options.machine-name')
      )
      ->addOption(
        'description',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.profile.options.description')
      )
      ->addOption(
        'include',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.lightning.subprofile.options.include')
      )
      ->addOption(
        'exclude',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.lightning.subprofile.options.exclude')
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    parent::initialize($input, $output);
    $options = $input->getOptions();

    if ($options['name']) {
      $this->validator->validateModuleName($options['name']);
    }
    if ($options['machine-name']) {
      $this->validator->validateMachineName($options['machine-name']);
    }
    if ($options['include']) {
      $this->include = $this->toArray($options['include']);
    }
    if ($options['exclude']) {
      // Only Lightning components can be excluded. This prevents wily users
      // from excluding modules that Lightning needs.
      $this->exclude = $this->toArray($options['exclude']);
      $this->validateExcludedModules($this->exclude);

      // Exclude the sub-components of any excluded components.
      $excluded_components = array_intersect_key(
        $this->getMainComponentInfo(),
        array_flip($this->exclude)
      );
      foreach ($excluded_components as $info) {
        $this->doExclude(@$info['components']);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);
    $options = $input->getOptions();

    // Get the profile name from the --name option, or ask the user if none was
    // specified.
    $profile = $options['name'] ?: $io->ask(
      $this->trans('commands.generate.profile.questions.profile'),
      NULL,
      [$this->validator, 'validateModuleName']
    );
    $input->setOption('name', $profile);

    // Get the machine name from the --machine-name option, or ask the user if
    // none was specified.
    $machine_name = $options['machine-name'] ?: $io->ask(
      $this->trans('commands.generate.profile.questions.machine-name'),
      $this->stringConverter->createMachineName($profile),
      [$this->validator, 'validateMachineName']
    );
    $input->setOption('machine-name', $machine_name);

    // Get the description from the --description option, or ask the user if
    // none was specified.
    $description = $options['description'] ?: $io->ask(
      $this->trans('commands.lightning.subprofile.questions.description'),
      NULL,
      'trim'
    );
    $input->setOption('description', $description);

    // Get any included modules from the --include option, or ask the user if
    // none were specified.
    if (empty($this->include)) {
      $include = $io->ask(
        $this->trans('commands.lightning.subprofile.options.include'),
        NULL,
        'trim'
      );
      $this->include = $this->toArray($include);
    }

    // Ask the user if they want to exclude any Lightning components.
    $collect_exclusions = $io->confirm(
      $this->trans('commands.lightning.subprofile.questions.collect-exclusions'),
      FALSE
    );
    if ($collect_exclusions) {
      // Ask about components that have not been excluded yet.
      $available_components = array_diff_key(
        $this->getMainComponentInfo(),
        array_flip($this->exclude)
      );
      foreach ($available_components as $component => $info) {
        $info['machine_name'] = $component;
        $this->confirmComponent($info, $io);
      }
    }
  }

  /**
   * Converts a comma-separated list into a clean array.
   *
   * @param string $list
   *   The comma-separated list.
   *
   * @return string[]
   *   The items in the list.
   */
  protected function toArray($list) {
    $list = trim($list);
    return $list ? array_map('trim', explode(',', $list)) : [];
  }

  /**
   * Validates excluded modules.
   *
   * @param string[] $excluded_modules
   *   The modules to be excluded.
   *
   * @throws \InvalidArgumentException
   *   If $excluded_modules contains anything that's not a Lightning component
   *   or sub-component.
   */
  public function validateExcludedModules(array $excluded_modules) {
    $components = $this->componentDiscovery->getAll();

    // Can't exclude Lightning Core.
    unset($components['lightning_core']);

    // Can't exclude anything that isn't a Lightning component or sub-component.
    $invalid = array_diff($excluded_modules, array_keys($components));

    if ($invalid) {
      $error = sprintf(
        $this->trans('commands.lightning.subprofile.errors.invalid-exclusions'),
        Element::oxford($invalid)
      );

      throw new \InvalidArgumentException($error);
    }
  }

  /**
   * Asks the user about including or excluding a Lightning component.
   *
   * @param array $info
   *   The parsed component info.
   * @param \Drupal\Console\Core\Style\DrupalStyle $io
   *   The I/O handler.
   */
  protected function confirmComponent(array $info, DrupalStyle $io) {
    // Include Lightning Core, or this won't be much of a sub-profile.
    if ($info['machine_name'] == 'lightning_core') {
      $include = TRUE;
    }
    else {
      $question = sprintf(
        $this->trans('commands.lightning.subprofile.questions.include-component'),
        $info['name']
      );
      $include = $io->confirm($question);
    }

    // If they want to include the component, ask about excluding individual
    // sub-components. Otherwise, exclude the component and its sub-components.
    if ($include) {
      if (isset($info['components'])) {
        $this->doExclude($this->excludeSubComponents($info, $io));
      }
    }
    else {
      $this->doExclude($info['machine_name']);
      $this->doExclude(@$info['components']);
    }
  }

  /**
   * Adds a set of modules to the exclude list.
   *
   * @param string|string[] $modules
   *   The module(s) to exclude.
   */
  protected function doExclude($modules) {
    if ($modules) {
      $modules = (array) $modules;
      $this->exclude = array_merge($this->exclude, $modules);
      $this->include = array_diff($this->include, $modules);
    }
  }

  /**
   * Asks the user about excluding a set of Lightning sub-components.
   *
   * @param array $parent
   *   The parent component info.
   * @param \Drupal\Console\Core\Style\DrupalStyle $io
   *   The I/O handler.
   *
   * @return string[]
   *   The sub-components to exclude.
   */
  protected function excludeSubComponents(array $parent, DrupalStyle $io) {
    $sub_components = $parent['components'];

    // Alphabetize for easier skimming.
    sort($sub_components);

    $question = sprintf(
      $this->trans('commands.lightning.subprofile.questions.exclude-components'),
      $parent['name'],
      count($sub_components),
      Element::oxford($sub_components)
    );

    if ($io->confirm($question, FALSE)) {
      $question = new ChoiceQuestion(
        $this->trans('commands.lightning.subprofile.questions.choose-excluded-components'),
        $sub_components
      );
      $question->setMultiselect(TRUE);

      return $io->askChoiceQuestion($question);
    }
    else {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    if ($this->confirmGeneration($io)) {
      $this->generator->generate(
        $input->getOption('name'),
        $input->getOption('machine-name'),
        $this->appRoot . '/profiles/custom',
        $input->getOption('description'),
        array_unique($this->include),
        array_unique($this->exclude)
      );
    }
  }

  /**
   * Returns info for top-level components.
   *
   * @return array[]
   *   Parsed info for all top-level Lightning components.
   */
  protected function getMainComponentInfo() {
    // Scanning for components and parsing their info is expensive, so cache it.
    if ($this->componentInfo === NULL) {
      $components = $this->componentDiscovery->getMainComponents();

      // Couldn't figure out how to sort the components with a uasort(). Went
      // with the quick and dirty way instead. Don't judge me!
      Element::order($components, [
        'lightning_core',
        'lightning_media',
        'lightning_layout',
        'lightning_workflow',
        'lightning_preview',
      ]);

      $this->componentInfo = array_map(
        function (Extension $component) {
          return $this->infoParser->parse($component->getPathname());
        },
        $components
      );
    }
    return $this->componentInfo;
  }

}
