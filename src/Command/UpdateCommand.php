<?php

namespace Drupal\lightning\Command;

use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\State\StateInterface;
use Drupal\lightning\Annotation\Update;
use phpDocumentor\Reflection\DocBlock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command {

  /**
   * The class resolver service.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The annotated class discovery handler.
   *
   * @var \Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery
   */
  protected $discovery;

  /**
   * The version from which we are updating.
   *
   * @var string
   */
  protected $since = NULL;

  /**
   * UpdateCommand constructor.
   *
   * @param ClassResolverInterface $class_resolver
   *   The class resolver service.
   * @param \Traversable $namespaces
   *   The namespaces to scan for updates.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(ClassResolverInterface $class_resolver, \Traversable $namespaces, StateInterface $state, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct('update:lightning');
    $this->classResolver = $class_resolver;
    $this->state = $state;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->discovery = new AnnotatedClassDiscovery('Update', $namespaces, Update::class);
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();

    $this
      ->addOption(
        'force',
        FALSE,
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

    $this->since = $input->getOption('force') ? '0.0.0' : $input->getOption('since');
  }

  protected function getDefinitions() {
    $definitions = $this->discovery->getDefinitions();
    ksort($definitions);

    $filter = function (array $definition) {
      $provider = $definition['provider'];

      return version_compare(
        $definition['id'],
        $this->since ?: $this->state->get("$provider.version", '0.0.0'),
        '>'
      );
    };

    return array_filter($definitions, $filter);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $definitions = $this->getDefinitions();

    if (sizeof($definitions) === 0) {
      return $output->writeln('There are no updates available.');
    }

    $io = new DrupalStyle($input, $output);
    $module_info = system_rebuild_module_data();
    $provider = NULL;

    foreach ($definitions as $id => $update) {
      if ($update['provider'] != $provider) {
        $provider = $update['provider'];
        $output->writeln($module_info[$provider]->info['name'] . ' ' . $update['id']);
      }

      $handler = $this->classResolver
        ->getInstanceFromDefinition($update['class']);

      $this->runTasks($handler, $io);

      $this->state->set("$provider.version", $update['id']);
    }
  }

  protected function runTasks($handler, DrupalStyle $io) {
    $tasks = $this->discoverTasks($handler);

    foreach ($tasks as $task) {
      /** @var \ReflectionMethod $reflector */
      /** @var DocBlock $doc_block */
      list ($reflector, $doc_block) = $task;

      if ($doc_block->hasTag('ask')) {
        $tags = $doc_block->getTagsByName('ask');

        $proceed = $io->confirm(reset($tags)->getContent());
        if ($proceed) {
          $reflector->invoke($handler, $io);
        }
      }
    }
  }

  protected function discoverTasks($handler) {
    $tasks = [];

    $methods = (new \ReflectionObject($handler))->getMethods(\ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
      $doc_comment = trim($method->getDocComment());

      if (empty($doc_comment)) {
        continue;
      }

      $doc_block = new DocBlock($doc_comment);

      if ($doc_block->hasTag('update')) {
        $tasks[] = [$method, $doc_block];
      }
    }
    return $tasks;
  }

}
