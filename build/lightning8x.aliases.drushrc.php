<?php

if (!isset($drush_major_version)) {
  $drush_version_components = explode('.', DRUSH_VERSION);
  $drush_major_version = $drush_version_components[0];
}
// Site lightning8x, environment dev
$aliases['dev'] = array(
  'root' => '/var/www/html/lightning8x.dev/docroot',
  'ac-site' => 'lightning8x',
  'ac-env' => 'dev',
  'ac-realm' => 'devcloud',
  'uri' => 'lightning8xxvzwnnmzyr.devcloud.acquia-sites.com',
  'remote-host' => 'free-4772.devcloud.hosting.acquia.com',
  'remote-user' => 'lightning8x.dev',
  'path-aliases' => array(
    '%drush-script' => 'drush' . $drush_major_version,
  )
);
$aliases['dev.livedev'] = array(
  'parent' => '@lightning8x.dev',
  'root' => '/mnt/gfs/lightning8x.dev/livedev/docroot',
);

if (!isset($drush_major_version)) {
  $drush_version_components = explode('.', DRUSH_VERSION);
  $drush_major_version = $drush_version_components[0];
}
// Site lightning8x, environment test
$aliases['test'] = array(
  'root' => '/var/www/html/lightning8x.test/docroot',
  'ac-site' => 'lightning8x',
  'ac-env' => 'test',
  'ac-realm' => 'devcloud',
  'uri' => 'lightning8xqkcojpcskz.devcloud.acquia-sites.com',
  'remote-host' => 'free-4772.devcloud.hosting.acquia.com',
  'remote-user' => 'lightning8x.test',
  'path-aliases' => array(
    '%drush-script' => 'drush' . $drush_major_version,
  )
);
$aliases['test.livedev'] = array(
  'parent' => '@lightning8x.test',
  'root' => '/mnt/gfs/lightning8x.test/livedev/docroot',
);
