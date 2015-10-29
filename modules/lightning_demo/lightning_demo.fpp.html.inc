<?php

/**
 * @file
 *  Migrations for Fieldable Panel Panes.
 */

class LightningDemoHTMLFPP extends Migration {

  public function __construct($arguments) {
    parent::__construct($arguments);
    $this->description = t('Import HTML FPPs.');

    // Create a map object for tracking the relationships between source rows
    $this->map = new MigrateSQLMap($this->machineName,
      array(
        'UUID' => array(
          'type' => 'varchar',
          'length' => 36,
          'not null' => FALSE,
          'description' => 'The Universally Unique Identifier.',
        )
      ),
      array(
        'uuid' => array(
          'type' => 'varchar',
          'length' => 36,
          'not null' => FALSE,
          'description' => 'The Universally Unique Identifier.',
        )
      )
    );

    // Create a MigrateSource object.
    if (isset($arguments['path'])) {
      $import_path = $arguments['path'];
    }
    else {
      $import_path = drupal_get_path('module', 'lightning_demo') . '/import/lightning_demo.fpp.html.csv';
    }
    $this->source = new MigrateSourceCSV($import_path, array(), array('header_rows' => 1));
    $this->destination = new MigrateDestinationFieldablePanelsPanes('raw_html', array('text_format' => 'raw_html'));

    $this->addFieldMapping('field_raw_html', 'Raw HTML');
    $this->addFieldMapping('title', 'Title');
    $this->addFieldMapping('uuid', 'UUID');
    $this->addFieldMapping('category', 'Category')->defaultValue('Add HTML');
  }

}
