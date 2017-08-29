<?php

namespace Drupal\lightning\Command;

use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Core\State\StateInterface;
use Drupal\lightning\UpdateManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command {

  /**
   * The interactive update plugin manager service.
   *
   * @var \Drupal\lightning\UpdateManager
   */
  protected $updateManager;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * UpdateCommand constructor.
   *
   * @param \Drupal\lightning\UpdateManager $update_manager
   *   The interactive update plugin manager service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(UpdateManager $update_manager, StateInterface $state) {
    parent::__construct('update:lightning');
    $this->updateManager = $update_manager;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();

    $this
      ->addOption(
        'force',
        NULL,
        InputOption::VALUE_NONE
      )
      ->addOption(
        'since',
        NULL,
        InputOption::VALUE_REQUIRED
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    parent::initialize($input, $output);

    if ($input->getOption('force')) {
      $input->setOption('since', '0.0.0');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $filter = function (array $definition) use ($input) {
      $provider = $definition['provider'];

      $current_version = $input->getOption('since') ?: $this->state->get("$provider.version", '0.0.0');
      $target_version = $definition['id'];

      return version_compare($target_version, $current_version, '>');
    };

    $updates = array_filter($this->updateManager->getDefinitions(), $filter);

    if (empty($updates)) {
      return $output->writeln('There are no updates available.');
    }

    $this->updateManager
      ->getFactory()
      ->setIO(
        new DrupalStyle($input, $output)
      );

    $module_info = system_rebuild_module_data();
    $provider = NULL;

    foreach ($updates as $id => $update) {
      if ($update['provider'] != $provider) {
        $provider = $update['provider'];
        $output->writeln($module_info[$provider]->info['name'] . ' ' . $update['id']);
      }
      $this->updateManager->createInstance($id)->execute();
      $this->state->set("$provider.version", $update['id']);
    }
  }

}
