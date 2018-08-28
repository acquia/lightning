<?php

namespace Drupal\lightning\Update;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\ProfileExtensionList;
use Drupal\Core\Serialization\Yaml;
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
   * The profile extension list service.
   *
   * @var \Drupal\Core\Extension\ProfileExtensionList
   */
  protected $profileList;

  /**
   * Update320 constructor.
   *
   * @param string $app_root
   *   The Drupal application root.
   * @param \Drupal\Core\Extension\ProfileExtensionList $profile_list
   *   The profile extension list service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface|NULL $translation
   *   (optional) The string translation service.
   */
  public function __construct($app_root, ProfileExtensionList $profile_list, TranslationInterface $translation = NULL) {
    $this->appRoot = $app_root;
    $this->profileList = $profile_list;

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
      $container->get('extension.list.profile'),
      $container->get('string_translation')
    );
  }

  /**
   * Converts sub-profile info keys to the 8.6.x API.
   *
   * @update
   *
   * @ask Do you want to update all sub-profiles to be Drupal 8.6-compatible?
   *
   * @param \Symfony\Component\Console\Style\StyleInterface $io
   *   The I/O handler.
   */
  public function updateProfiles(StyleInterface $io) {
    $discovery = new ExtensionDiscovery($this->appRoot);

    foreach ($discovery->scan('profile') as $profile) {
      $info_file = $profile->getPathname();

      if (! is_writeable($info_file)) {
        $message = $this->t('Cannot write to @path, skipping.', [
          '@path' => $info_file,
        ]);
        $io->warning((string) $message);
        continue;
      }

      $info = file_get_contents($info_file);
      if (empty($info) || ! strstr($info, 'base profile:')) {
        continue;
      }

      $info = Yaml::decode($info);

      // 'base profile' must be an array with at least the 'name' key.
      if (! isset($info['base profile']['name'])) {
        continue;
      }

      // Build the list of excluded extensions and themes.
      $exclude = [];
      if (isset($info['base profile']['excluded_dependencies'])) {
        $exclude = array_merge($exclude, $info['base profile']['excluded_dependencies']);
      }
      if (isset($info['base profile']['excluded_themes'])) {
        $exclude = array_merge($exclude, $info['base profile']['excluded_themes']);
      }

      $info['base profile'] = $info['base profile']['name'];
      if ($exclude) {
        $info['exclude'] = $exclude;
      }

      $success = file_put_contents($info_file, Yaml::encode($info));
      if ($success) {
        $message = $this->t('Updated @profile.', [
          '@profile' => $profile->getName(),
        ]);
        $io->success((string) $message);
      }
      else {
        throw new \RuntimeException('An error occurred writing to ' . $profile->getPathname());
      }
    }
    // Reset the cached profile list.
    $this->profileList->reset();
  }

}
