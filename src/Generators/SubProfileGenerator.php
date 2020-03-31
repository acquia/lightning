<?php

namespace Drupal\lightning\Generators;

use Drupal\lightning\ComponentDiscovery;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class SubProfileGenerator extends BaseGenerator
{
  /**
   * The Lightning component discovery helper.
   *
   * @var \Drupal\lightning\ComponentDiscovery
   */
  protected $componentDiscovery;

  /**
   * Modules to include in the sub-profile.
   *
   * @var string[]
   */
  protected $installList = [];

  protected $name = 'lightning-subprofile';
  protected $description = 'Generates a Lightning Subprofile.';
  protected $alias = 'lsp';
  protected $templatePath = __DIR__;
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
  protected function interact(InputInterface $input, OutputInterface $output)
  {
    $questions['name'] = new Question('Profile Name');
    $questions['name']->setValidator([Utils::class, 'validateRequired']);

    $questions['machine_name'] = new Question('Profile Machine Name (enter for default)');
    $questions['machine_name']->setValidator([Utils::class, 'validateMachineName']);
    $questions['description'] = new Question('Enter the description (optional)', NULL);
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
      $questions['exclude']->setMultiselect(true);
      $this->collectVars($input, $output, $questions);
    }

    $this->addFile()
      ->path('custom/{machine_name}/{machine_name}.info.yml')
      ->template('info.yml.twig');

    $this->addFile()
      ->path('custom/{machine_name}/install.info.yml')
      ->template('install.twig');

    $this->addFile()
      ->path('custom/{machine_name}/profile.info.yml')
      ->template('profile.twig');

  }

}
