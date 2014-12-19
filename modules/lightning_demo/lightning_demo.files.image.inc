<?php

/**
 * @file
 *  Migrations for Lightning Demo Image Files.
 */

class LightningDemoFilesImage extends Migration {
  public function __construct($arguments) {
    parent::__construct($arguments);
    $this->map = new MigrateSQLMap($this->machineName,
      array(
        'image' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
        ),
      ),
      MigrateDestinationFile::getKeySchema()
    );
    $this->destination = new MigrateDestinationFile('image');
    $import_path = drupal_get_path('module', 'lightning_demo') . '/import/';
    $this->source = new MigrateSourceCSV($import_path . 'lightning_demo.files.image.csv', $this->csvcolumns(), array('header_rows' => 1));
    $this->addFieldMapping('value', 'image');
    $this->addFieldMapping('file_replace')->defaultValue(FILE_EXISTS_REPLACE);
    $this->addFieldMapping('source_dir')->defaultValue($import_path . 'images');
    $this->addFieldMapping('destination_file', 'image'); 
  }

  function csvcolumns() {
    $columns[0] = array('image', 'Image');
    return $columns;
  }

}

