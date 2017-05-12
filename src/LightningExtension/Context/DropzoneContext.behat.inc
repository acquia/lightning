<?php

namespace Acquia\LightningExtension\Context;

use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Contains step definitions for interacting with DropzoneJS widgets.
 */
class DropzoneContext extends DrupalSubContextBase {

  /**
   * The Mink context.
   *
   * @var MinkContext
   */
  protected $minkContext;

  /**
   * Gathers required contexts.
   *
   * @BeforeScenario
   */
  public function gatherContexts() {
    $this->minkContext = $this->getContext(MinkContext::class);
  }

  /**
   * Attaches a file to a dropzone.
   *
   * @param string $file
   *   The file to attach, relative to the file directory configured for Mink.
   *
   * @When I attach the file :file to the dropzone
   */
  public function attachFileToDropzone($file) {
    $this->getSession()
      ->executeScript('Dropzone.instances[0].hiddenFileInput.name = "file"');

    $this->minkContext->attachFileToField('file', $file);

    // TODO: React when the upload actually completes.
    sleep(3);
  }

}
