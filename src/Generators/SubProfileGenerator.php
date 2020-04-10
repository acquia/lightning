<?php

namespace Drupal\lightning\Generators;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\ModuleExtensionList;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\Utils;
use Drush\Style\DrushStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Implements lightning-subprofile command.
 */
final class SubProfileGenerator extends BaseGenerator {

  /**
   * The module extension list service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  private $moduleList;

  /**
   * {@inheritdoc}
   */
  protected $name = 'lightning-subprofile';

  /**
   * {@inheritdoc}
   */
  protected $description = 'Generates a Lightning Subprofile.';

  /**
   * {@inheritdoc}
   */
  protected $alias = 'lsp';

  /**
   * {@inheritdoc}
   */
  protected $templatePath = __DIR__;

  /**
   * {@inheritdoc}
   */
  protected $destination = 'profiles';

  /**
   * SubProfileGenerator constructor.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_list
   *   The module extension list service.
   */
  public function __construct(ModuleExtensionList $module_list) {
    parent::__construct();
    $this->moduleList = $module_list;
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $io = new DrushStyle($input, $output);

    $questions['name'] = new Question('Profile Name');
    $questions['name']->setValidator([Utils::class, 'validateRequired']);
    $questions['machine_name'] = new Question('Profile Machine Name (enter for default)');
    $questions['machine_name']->setValidator([Utils::class, 'validateMachineName']);
    $questions['description'] = new Question('Enter the description (optional)');
    $questions['install'] = new Question('Additional modules to include (optional), separated by commas (e.g. context, rules, file_entity)', NULL);
    $questions['install']->setNormalizer([static::class, 'toArray']);

    $vars = &$this->collectVars($input, $output, $questions);

    if ($io->confirm('Do you want to exclude any components of Lightning?', FALSE)) {
      $filter = function ($name) {
        return strpos($name, 'lightning_') === 0;
      };
      $modules = array_filter($this->moduleList->getAllAvailableInfo(), $filter, ARRAY_FILTER_USE_KEY);

      $map = function (array $info) {
        return $info['name'];
      };
      $modules = array_map($map, $modules);

      $questions['exclude'] = new ChoiceQuestion('Lightning components to exclude (optional), entered as keys separated by commas (e.g. lightning_media, lightning_layout)', $modules);
      $questions['exclude']->setMultiselect(TRUE);

      $this->collectVars($input, $output, $questions);
    }

    $info_array = [
      'name' => $vars['name'],
      'type' => 'profile',
      'description' => '',
      'core_version_requirement' => '^8.8 || ^9',
      'install' => [],
      'themes' => [
        'bartik', 'seven',
      ],
      'base profile' => 'lightning',
      'exclude' => [],
    ];

    if ($vars['description']) {
      $info_array['description'] = $vars['description'];
    }
    if ($vars['install']) {
      $info_array['install'] = $vars['install'];
    }
    if ($vars['exclude']) {
      $info_array['exclude'] = is_string($vars['exclude'])
        ? static::toArray($vars['exclude'])
        : $vars['exclude'];
    }

    $info_array = array_filter($info_array);

    $profile_path = 'custom/{machine_name}';
    // For reasons I don't understand, if an output directory is specified
    // (e.g., in our test coverage), then the $destination property of this
    // class is ignored. This works around that particular bit of weirdness.
    if ($input->getOption('directory')) {
      $profile_path = "profiles/$profile_path";
    }

    $this->addFile()
      ->path("$profile_path/{machine_name}.info.yml")
      ->content(Yaml::encode($info_array));

    $this->addFile()
      ->path("$profile_path/{machine_name}.install")
      ->template('install.twig');

    $this->addFile()
      ->path("$profile_path/{machine_name}.profile")
      ->template('profile.twig');
  }

  /**
   * Converts a comma-separated string to an array of trimmed values.
   *
   * @param string $string
   *   The comma-separated string to split up.
   *
   * @return string[]
   *   The comma-separated values, trimmed of white space.
   */
  public static function toArray($string) {
    return $string ? array_map('trim', explode(',', $string)) : [];
  }

}
