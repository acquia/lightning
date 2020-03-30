<?php

namespace Drupal\lightning\Generators;

use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SubProfileGenerator extends BaseGenerator
{

  protected $name = 'lightning-subprofile';
  protected $description = 'Generates a Lightning Subprofile.';
  protected $alias = 'lsp';
  protected $templatePath = __DIR__;
  protected $destination = 'profiles';

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output)
  {
    $questions['name'] = new Question('Profile Name');
    $questions['name']->setValidator([Utils::class, 'validateRequired']);
    $questions['machine_name'] = new Question('Profile Machine Name');
    $questions['machine_name']->setValidator([Utils::class, 'validateMachineName']);
    $questions['description'] = new Question('Enter the description (optional)', NULL);
    $questions['install'] = new Question('Additional modules to include (optional), separated by commas (e.g. context, rules, file_entity)', NULL);
    $questions['install']->setNormalizer(function ($answer) {
      return $answer ? array_map('trim', explode(',', $answer)) : [];
    });
    $questions['exclude'] = new Question('Lightning components to exclude (optional), separated by commas (e.g. lightning_workflow, lightning_media)', Null);
    $questions['exclude']->setNormalizer(function ($answer) {
      return $answer ? array_map('trim', explode(',', $answer)) : [];
    });

    $vars = &$this->collectVars($input, $output, $questions);

    $this->addFile()
      ->path('contrib/{machine_name}/{machine_name}.info.yml')
      ->template('info.yml.twig');

    $this->addFile()
      ->path('contrib/{machine_name}/install.info.yml')
      ->template('install.twig');

    $this->addFile()
      ->path('contrib/{machine_name}/profile.info.yml')
      ->template('profile.twig');

  }
  
}
