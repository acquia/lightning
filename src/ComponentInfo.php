<?php

namespace Drupal\lightning;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\InfoParserInterface;

/**
 * Helper object to collect info about Lightning components and sub-components.
 */
class ComponentInfo extends ComponentDiscovery {

  /**
   * The info file parser.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * Cache of parsed component info.
   *
   * @var array[]
   */
  protected $info;

  /**
   * ComponentInfo constructor.
   *
   * @param string $app_root
   *   The application root directory.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info file parser.
   */
  public function __construct($app_root, InfoParserInterface $info_parser) {
    parent::__construct($app_root);
    $this->infoParser = $info_parser;
  }

  /**
   * {@inheritdoc}
   */
  public function getAll() {
    if (is_null($this->info)) {
      $parse = function (Extension $component) {
        return $this->infoParser->parse($component->getPathname());
      };

      $this->info = array_map($parse, parent::getAll());
    }
    return $this->info;
  }

  /**
   * {@inheritdoc}
   */
  public function getMainComponents() {
    return array_intersect_key($this->info, parent::getMainComponents());
  }

  /**
   * {@inheritdoc}
   */
  public function getSubComponents() {
    return array_intersect_key($this->info, parent::getSubComponents());
  }

}
