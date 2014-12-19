<?php

/**
 * @file
 *  Migrations for Lightning Demo Article Nodes.
 */

class LightningDemoNodesArticle extends ImportBaseNodes {

  public function __construct($arguments) {
    parent::__construct($arguments);
    $this->description = t('Import Lightning Demo pages');
    $import_path = drupal_get_path('module', 'lightning_demo') . '/import/';

    // Create a MigrateSource object.
    $this->source = new MigrateSourceCSV($import_path . 'lightning_demo.nodes.article.csv', $this->csvcolumns(), array('header_rows' => 1));
    $this->destination = new MigrateDestinationNode('article');

    // Set body:format
    $this->addFieldMapping('body:format')->defaultValue('full_html');

    // Path
    $this->addFieldMapping('path', 'path');
   
    // Workbench
    $this->addFieldMapping('workbench_moderation_state_new', 'workbench_moderation_state_new')->defaultValue('published');
  
    // Comments
    $this->addFieldMapping('comment', 'comment')->defaultValue(0);
  }

  function csvcolumns() {
    $columns[0] = array('title', 'Title');
    $columns[1] = array('body', 'Body');
    $columns[2] = array('path', 'Path');
    return $columns;
  }

}

