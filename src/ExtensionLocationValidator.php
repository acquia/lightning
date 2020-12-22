<?php

namespace Drupal\lightning;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;

/**
 * Validates that Lightning does not physically contain installed extensions.
 *
 * @internal
 *   This class is a completely internal part of Lightning's uninstall system
 *   and can be changed in any way, or removed outright, at any time without
 *   warning. External code should not use this class in any way.
 */
final class ExtensionLocationValidator implements ModuleUninstallValidatorInterface {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  private $themeHandler;

  /**
   * ExtensionLocationValidator constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler service.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) {
    $reasons = [];

    if ($module === 'lightning') {
      $extensions = array_merge(
        $this->moduleHandler->getModuleList(),
        $this->themeHandler->listInfo()
      );

      $profile_path = $extensions[$module]->getPath();
      unset($extensions[$module]);

      $filter = function (Extension $extension) use ($profile_path) : bool {
        return strpos($extension->getPath(), $profile_path) !== FALSE;
      };
      $extensions = array_filter($extensions, $filter);

      if ($extensions) {
        $extensions = array_keys($extensions);
        $reasons[] = sprintf('The following modules and/or themes are located inside the Lightning profile directory. They must be moved elsewhere before Lightning can be uninstalled: %s', implode(', ', $extensions));
      }
    }
    return $reasons;
  }

}
