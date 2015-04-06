<?php

/**
 *  @file
 *   Contains base user migration class.
 */

class LightningDemoUsers extends ImportBaseUsers {

  public function __construct($arguments) {
    parent::__construct($arguments);

    $import_path = drupal_get_path('module', 'lightning_demo') . '/import/';
    $this->source = new MigrateSourceCSV($import_path . 'lightning_demo.users.csv', $this->csvcolumns(), array('header_rows' => 1));

  }

  function csvcolumns() {
    $columns[0] = array('name', 'Name');
    $columns[1] = array('pass', 'Pass');
    $columns[2] = array('mail', 'Mail');
    $columns[3] = array('status', 'Status');
    $columns[4] = array('roles', 'Roles');
    return $columns;
  }

  public function prepareRow($row) {
    $user_roles = explode(",", $row->roles);
    $roles = array('2' => '2');
    foreach ($user_roles as $role_name) {
      $rid = db_query('SELECT rid FROM {role} WHERE name = :name', array(':name' => $role_name))->fetchField();
      $roles[$rid] = $rid;
    }
    $row->roles = $roles;
    return TRUE;
  }

}

