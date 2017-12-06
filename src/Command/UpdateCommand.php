<?php

namespace Drupal\lightning\Command;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\lightning\Annotation\Update;
use phpDocumentor\Reflection\DocBlock;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command {

  /**
   * The class resolver service.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * The update discovery object.
   *
   * @var DiscoveryInterface
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
   * @param DiscoveryInterface $discovery
   *   (optional) The update discovery handler.
   */
  public function __construct(ClassResolverInterface $class_resolver, \Traversable $namespaces, DiscoveryInterface $discovery = NULL) {
    parent::__construct('update:lightning');
    $this->classResolver = $class_resolver;
    $this->discovery = $discovery ?: new AnnotatedClassDiscovery('Update', $namespaces, Update::class);
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();

    $this->addArgument(
      'since',
      InputArgument::REQUIRED,
      'The version of Lightning you are updating from, in semantic version format (major.minor.patch). To run all updates since the beginning of time, use 0.0.0.'
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    parent::initialize($input, $output);
    $this->since = $input->getArgument('since');
  }

  protected function getDefinitions() {
    $definitions = $this->discovery->getDefinitions();
    ksort($definitions);

    $filter = function (array $definition) {
      return version_compare($definition['id'], $this->since, '>');
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
    $output->writeln("Executing all updates since version $this->since.");

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
    }
  }

  protected function runTasks($handler, DrupalStyle $io) {
    $tasks = $this->discoverTasks($handler);

    foreach ($tasks as $task) {
      /** @var \ReflectionMethod $reflector */
      /** @var DocBlock $doc_block */
      list ($reflector, $doc_block) = $task;

      $proceed = TRUE;
      if ($doc_block->hasTag('ask')) {
        $tags = $doc_block->getTagsByName('ask');
        $proceed = $io->confirm(reset($tags)->getContent());
      }

      if ($proceed) {
        $reflector->invoke($handler, $io);
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
