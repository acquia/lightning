#!/bin/bash

# Copy the local settings file.
cp docroot/sites/example.settings.local.php docroot/sites/default/settings.local.php

# Append a default DB to the local settings file
cat <<HEREDOC >> docroot/sites/default/settings.local.php

// For a single database configuration, the following is sufficient:
\$databases['default']['default'] = [
 'driver' => 'mysql',
 'database' => 'lightning',
 'username' => 'root',
 'password' => 'password',
 'host' => 'localhost',
 'collation' => 'utf8mb4_general_ci',
];
HEREDOC

# Copy the settings.php file.
cp docroot/sites/default/default.settings.php docroot/sites/default/settings.php

# Enable local settings.
cat <<HEREDOC >> docroot/sites/default/settings.php

if (file_exists(__DIR__ . '/settings.local.php')) {
 include __DIR__ . '/settings.local.php';
}
HEREDOC

# Copy the settings.php file.
cp docroot/sites/default/default.services.yml docroot/sites/default/services.yml

# Make the files directory.
mkdir docroot/sites/default/files
chmod -R 777 docroot/sites/default/files

# Notify users that they want to modify their local settings.
echo "Edit docroot/sites/default/settings.local.php with your settings."
echo "If the defaults are ok, you can install the site by running 'drush si lightning' from /docroot."
