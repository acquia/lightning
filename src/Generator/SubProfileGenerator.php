<?php

namespace Drupal\lightning\Generator;

use Drupal\Console\Core\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Generates a Lightning-subprofile.
 */
class SubProfileGenerator extends Generator {

  /**
   * {@inheritdoc}
   */
  public function generate($name, $machine_name, $profile_path, $description, array $install_list, array $exclude_list
  ) {
    $destination = $profile_path . '/' . $machine_name;

    // If the destination path already exists, realpath() will return something
    // truthy. Which means we need to look a little closer...
    $dir = realpath($destination);
    if ($dir) {
      if (is_dir($dir)) {
        if (scandir($dir) != ['.', '..']) {
          throw new \RuntimeException(
            sprintf('Unable to generate the profile as the target directory "%s" is not empty.', $dir)
          );
        }
        if (!is_writable($dir)) {
          throw new \RuntimeException(
            sprintf('Unable to generate the profile as the target directory "%s" is not writable.', $dir)
          );
        }
      }
      else {
        throw new \RuntimeException(
          sprintf('Unable to generate the profile as the target directory "%s" exists but is a file.', $dir)
        );
      }
    }
    else {
      // Use the Filesystem component to create the destination recursively.
      (new Filesystem)->mkdir($destination);

      // Recurse to do the entire validation dance again.
      $this->generate($name, $machine_name, $profile_path, $description, $install_list, $exclude_list);
      return;
    }

    $parameters = [
      'profile' => $name,
      'machine_name' => $machine_name,
      'description' => $description,
      'install' => $install_list,
      'exclude' => $exclude_list,
    ];

    $prefix = "$dir/$machine_name";

    $this->renderFile('profile/info.yml.twig', "$prefix.info.yml", $parameters);
    $this->renderFile('profile/profile.twig', "$prefix.profile", $parameters);
    $this->renderFile('profile/install.twig', "$prefix.install", $parameters);
  }

}
