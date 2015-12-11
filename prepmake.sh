#!/usr/bin/env php
<?php

function to_ini_format(array $in, array $keys = []) {
  $out = array();

  foreach ($in as $key => $value) {
    if (is_array($value)) {
      $out = array_merge($out, to_ini_format($value, array_merge($keys, [$key])));
    }
    else {
      if ($keys) {
        $key_string = reset($keys) . '[' . implode('][', array_slice($keys, 1)) . '][' . $key . ']';
      }
      else {
        $key_string = $key;
      }
      $out[] = "$key_string = $value";
    }
  }

  return $out;
}

$files = glob('*.make.yml');
foreach ($files as $file) {
  $parsed = yaml_parse_file($file);
  $out = fopen(basename($file, '.yml'), 'w');
  fwrite($out, "# This file was automatically generated from $file. Do not edit, or Krampus will get you!\n");
  foreach (to_ini_format($parsed) as $line) {
    fwrite($out, "$line\n");
  }
  fclose($out);
}
