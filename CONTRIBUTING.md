## Contributing to Lightning

### Local development
**Note that these instructions won't work on old, unsupported branches of Lightning.** At the time of this writing, that includes the `8.x-1.x` and `8.x-2.x` branches, which have long since reached the end of their lives.

This documentation describes how to set up Lightning (or any of its components) for development on a machine running a Unix-like operating system (e.g., Linux or macOS). We assume that:

* You have Git installed in your PATH. You can confirm this by running `git --version`.
* You have PHP 7.1 or later installed in your PATH. You can confirm this by running `php --version`.
* You have Composer installed in your PATH. You can confirm this by running `composer --version`. You should also have Composer's global binary directory (usually `$HOME/.composer/vendor/bin`) in your PATH.
* You will need `drush/drush-launcher` globally installed. To confirm this, run `drush --version`. If the command is not found, run `composer global require drush/drush-launcher`.
* You will also need a database server installed. Lightning uses SQLite by default for development, since it is the most lightweight option supported by Drupal core.

Now, get your Lightning code base set up:

1. Clone the git repository, e.g. `git clone git@github.com:acquia/lightning.git`
2. Enter the repository and run `composer install` to install all dependencies.
3. Install Lightning and all necessary components by running `./install-drupal.sh`. By default, this will try to install a SQLite database file called `db.sqlite` in the `docroot` directory. You can override this by passing a `DB_URL` environment variable to `install-drupal.sh`, containing the Drush-compatible URL of the database you want to use. For example:

```
DB_URL=mysql://user:password@server/drupal ./install-drupal.sh
```
4. Run the web server. The quickest option is to use PHP's built-in server: `drush runserver 8080`
5. You should now be able to access your Lightning site at `http://localhost:8080`.
