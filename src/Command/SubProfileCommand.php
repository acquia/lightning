<?php

namespace Drupal\lightning\Command;

use Drupal\Console\Command\Shared\ConfirmationTrait;
use Drupal\Console\Core\Command\Shared\CommandTrait;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\Console\Utils\TranslatorManager;
use Drupal\Console\Utils\Validator;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\lightning\ComponentInfo;
use Drupal\lightning_core\Element;
use Drupal\lightning\Generator\SubProfileGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Drupal\Console\Core\Utils\TwigRenderer;

/**
 * A Drupal Console command to generate a Lightning sub-profile.
 */
class SubProfileCommand extends Command {

  use CommandTrait;
  use ConfirmationTrait;

  /**
   * The Lightning component information gatherer.
   *
   * @var ComponentInfo
   */
  protected $componentInfo;

  /**
   * The profile generator.
   *
   * @var SubProfileGenerator
   */
  protected $generator;

  /**
   * The string converter.
   *
   * @var StringConverter
   */
  protected $stringConverter;

  /**
   * The validation service.
   *
   * @var Validator
   */
  protected $validator;

  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * SubProfileCommand constructor.
   *
   * @param SubProfileGenerator $profile_generator
   *   The profile generator.
   * @param StringConverter $string_converter
   *   The string converter.
   * @param Validator $validator
   *   The validation service.
   * @param string $app_root
   *   The Drupal application root.
   * @param InfoParserInterface $info_parser
   *   The info file parser.
   * @param TranslatorManager $translator
   *   (optional) The translator manager.
   */
  public function __construct(SubProfileGenerator $profile_generator, StringConverter $string_converter, Validator $validator, $app_root, InfoParserInterface $info_parser, TranslatorManager $translator = NULL) {
    parent::__construct('lightning:subprofile');
    $this->componentInfo = new ComponentInfo($app_root, $info_parser);

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

    $name = $input->getOption('name');
    if ($name) {
      $this->validator->validateModuleName($name);
    }

    $machine_name = $input->getOption('machine-name');
    if ($machine_name) {
      $this->validator->validateMachineName($machine_name);
    }

    // Process and validate the list of excluded modules.
    $exclude = $this->toArray($input->getOption('exclude'));
    if ($exclude) {
      // Only Lightning components can be excluded. This prevents wily users
      // from excluding modules that Lightning needs.
      $this->validateExcludedModules($exclude);
    }
    $input->setOption('exclude', $exclude);
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    // Get the profile name from the --name option, or ask the user if none was
    // specified.
    $profile = $input->getOption('name') ?: $io->ask(
      $this->trans('commands.generate.profile.questions.profile'),
      NULL,
      [$this->validator, 'validateModuleName']
    );
    $input->setOption('name', $profile);

    // Get the machine name from the --machine-name option, or ask the user if
    // none was specified.
    $machine_name = $input->getOption('machine-name') ?: $io->ask(
      $this->trans('commands.generate.profile.questions.machine-name'),
      $this->stringConverter->createMachineName($profile),
      [$this->validator, 'validateMachineName']
    );
    $input->setOption('machine-name', $machine_name);

    // Get the description from the --description option, or ask the user if
    // none was specified.
    $description = $input->getOption('description') ?: $io->ask(
      $this->trans('commands.generate.profile.questions.description'),
      NULL,
      'trim'
    );
    $input->setOption('description', $description);

    // Get any included modules from the --include option, or ask the user if
    // none were specified.
    $include = $input->getOption('include');
    if (empty($include)) {
      $include .= $io->ask(
        $this->trans('commands.lightning.subprofile.options.include'),
        NULL,
        'trim'
      );
    }
    $input->setOption('include', $this->toArray($include));

    $exclude = $input->getOption('exclude');

    foreach ($this->getMainComponentInfo() as $component => $info) {
      // If the component has been excluded, exclude its sub-components as well.
      if (in_array($component, $exclude)) {
        $exclude = array_merge($exclude, [$component], (array) @$info['components']);
      }
      else {
        $info['machine_name'] = $component;
        $exclude = array_merge($exclude, $this->excludeComponent($info, $io));
      }
    }
    $input->setOption('exclude', $exclude);
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
    $components = $this->getComponentInfo();

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
   * Asks the user about excluding a Lightning component.
   *
   * @param array $info
   *   The parsed component info.
   * @param \Drupal\Console\Core\Style\DrupalStyle $io
   *   The I/O handler.
   *
   * @return string[]
   *   The modules to exclude.
   */
  protected function excludeComponent(array $info, DrupalStyle $io) {
    // Assume we will include the component if it's not experimental.
    $include = empty($info['experimental']);

    // We don't ask about including Lightning Core, or this won't be much of a
    // Lightning sub-profile.
    if ($info['machine_name'] != 'lightning_core') {
      $question = sprintf(
        $this->trans('commands.lightning.subprofile.questions.include-component'),
        $info['name']
      );
      if (isset($info['experimental'])) {
        $question .= sprintf(
          ' <fg=yellow>%s</>',
          $this->trans('commands.lightning.subprofile.experimental')
        );
      }
      $include = $io->confirm($question, $include);
    }

    // If the user declined the component, return the component and all of its
    // sub-components.
    if (empty($include)) {
      return array_merge((array) $info['name'], (array) @$info['components']);
    }
    // Otherwise, ask about the individual sub-components.
    elseif (isset($info['components'])) {
      return $this->excludeSubComponents($info['components'], $info, $io);
    }
    // In all other cases, we're not excluding anything.
    else {
      return [];
    }
  }

  /**
   * Asks the user about excluding a set of Lightning sub-components.
   *
   * @param string[] $sub_components
   *   The sub-components to ask about.
   * @param array $parent
   *   The parent component info.
   * @param \Drupal\Console\Core\Style\DrupalStyle $io
   *   The I/O handler.
   *
   * @return string[]
   *   The modules to exclude.
   */
  protected function excludeSubComponents(array $sub_components, array $parent, DrupalStyle $io) {
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

    if (!$this->confirmGeneration($io)) {
      return;
    }

    $profile = $this->validator->validateModuleName($input->getOption('name'));
    $machine_name = $this->validator->validateMachineName($input->getOption('machine-name'));
    $description = $input->getOption('description');
    $profile_path = $this->appRoot . '/profiles/custom';
    $include = $input->getOption('include');

    // Check if all module dependencies are available.
    $dependencies = $this->validator->validateModuleDependencies($include);
    if ($dependencies) {
      // @todo ProfileCommand::checkDependencies is private so we aren't
      // actually checking to see if they are present.
      $include = $dependencies['success'];
    }
    $this->generator->generate(
      $profile,
      $machine_name,
      $profile_path,
      $description,
      $include,
      $input->getOption('exclude')
    );
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

    // Ensure that sub-components know their parent.
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
   * @return array[]
   *   Parsed info for all top-level Lightning components.
   */
  protected function getMainComponentInfo() {
    $main_components = array_filter($this->getComponentInfo(), function (array $info) {
      return empty($info['parent']);
    });

    // Couldn't figure out how to sort the components with a uasort() function.
    // Went with the quick and dirty way instead. Don't judge me!
    Element::order($main_components, [
      'lightning_core',
      'lightning_media',
      'lightning_layout',
      'lightning_workflow',
      'lightning_preview',
    ]);
    return $main_components;
  }

}
