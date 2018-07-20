<?php

namespace Acquia\Lightning;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Update("3.2.0")
 */
final class Update320 implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * Update320 constructor.
   *
   * @param string $app_root
   *   The Drupal application root.
   * @param \Drupal\Core\StringTranslation\TranslationInterface|NULL $translation
   *   (optional) The string translation service.
   */
  public function __construct($app_root, TranslationInterface $translation = NULL) {
    $this->appRoot = $app_root;

    if ($translation) {
      $this->setStringTranslation($translation);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('app.root'),
      $container->get('string_translation')
    );
  }

  /**
   * Converts sub-profile info keys to the 8.6.x API.
   *
   * @update
   *
   * @ask Do you want to update all sub-profiles to be Drupal 8.6 compatible?
   *
   * @param \Symfony\Component\Console\Style\StyleInterface $io
   *   The I/O handler.
   */
  public function updateProfiles(StyleInterface $io) {
    $discovery = new ExtensionDiscovery($this->appRoot);

    $profiles = $discovery->scan('profile');
    foreach ($profiles as $profile) {
      $info_file = $profile->getPathname();

      if (is_writeable($info_file)) {
        $info = file_get_contents($info_file);

        if (strstr($info, 'base profile:')) {
          $info = str_replace(['dependencies:', 'excluded_dependencies:'], ['install:', 'exclude:'], $info);
          file_put_contents($info_file, $info);

          $message = $this->t('Updated @profile.', [
            '@profile' => $profile->getName(),
          ]);
          $io->success((string) $message);
        }
      }
      else {
        $message = $this->t('Cannot write to @path, skipping.', [
          '@path' => $info_file,
        ]);
        $io->warning((string) $message);
      }
    }
  }

}
