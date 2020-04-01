<?php

namespace Drupal\lightning\Generators;

use Drupal\Component\Serialization\Yaml;
use Drupal\lightning\ComponentDiscovery;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Implements lightning-subprofile command.
 */
final class SubProfileGenerator extends BaseGenerator {
  /**
   * The Lightning component discovery helper.
   *
   * @var \Drupal\lightning\ComponentDiscovery
   */
  protected $componentDiscovery;

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
   * @param string $app_root
   *   The Drupal application root.
   */
  public function __construct($app_root) {
    parent::__construct();
    $this->componentDiscovery = new ComponentDiscovery($app_root);
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $questions['name'] = new Question('Profile Name');
    $questions['name']->setValidator([Utils::class, 'validateRequired']);
    $questions['machine_name'] = new Question('Profile Machine Name (enter for default)');
    $questions['machine_name']->setValidator([Utils::class, 'validateMachineName']);
    $questions['description'] = new Question('Enter the description (optional)');
    $questions['install'] = new Question('Additional modules to include (optional), separated by commas (e.g. context, rules, file_entity)', NULL);
    $normalizer = function ($answer) {
      return $answer ? array_map('trim', explode(',', $answer)) : [];
    };
    $questions['install']->setNormalizer($normalizer);
    $questions['exclusions'] = new ConfirmationQuestion('Do you want to exclude any components of Lightning?', FALSE);

    $vars = &$this->collectVars($input, $output, $questions);

    if ($vars['exclusions']) {
      $modules = $this->componentDiscovery->getAll();
      $questions['exclude'] = new ChoiceQuestion(
        'Lightning components to exclude (optional), entered as keys separated by commas (e.g. 0,1)',
        array_keys($modules),
        NULL
      );
      $questions['exclude']->setMultiselect(TRUE);
      $this->collectVars($input, $output, $questions);
    }

    $info_array = [
      'name' => $vars['name'],
      'type' => 'profile',
      'description' => [],
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
      $info_array['exclude'] = $vars['exclude'];
    }

    $info_array = array_filter($info_array);

    $this->addFile()
      ->path('custom/{machine_name}/{machine_name}.info.yml')
      ->content(Yaml::encode($info_array));

    $this->addFile()
      ->path('custom/{machine_name}/install.info.yml')
      ->template('install.twig');

    $this->addFile()
      ->path('custom/{machine_name}/profile.info.yml')
      ->template('profile.twig');

  }

}
