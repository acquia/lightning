## Lightning Release Tags
Lightning uses a three-part semantic versioning nomenclature within the
constraints of drupal.org's current capabilities. See
[this drupal.org issue](https://www.drupal.org/node/1612910) for the latest.

The three parts of our versioning system are MAJOR.FEATURE.SPRINT. However, due
to the current constraints of drupal.org, there is no separator between the
FEATURE and SPRINT digits. This also limits Lightning to ten sprint releases
before releasing a new feature.

Given the following tag: 8.x-1.23:

|       |                              |
|-------|------------------------------|
| __8__ | Major version of Drupal Core |
| __x__ |  |
| __1__ | Major version of Lightning |
| __2__ | Feature release of Lightning. Also increments with minor core releases. |
| __3__ | Sprint release between feature releases. |

Lightning typically makes a sprint release every four weeks. We'll also use
sprint releases to package new minor releases of Drupal Core with Lightning as
they become available. When this happens, we will also increment the feature
release/minor version number of Lightning - about once every six months.

Starting with the 2.x branch of lightning, we will also push proper [SemVer tags](http://semver.org/)
to GitHub which is the source for the Main PHP Packagist where [Lightning Project](https://github.com/acquia/lightning-project)
will fetch Lightning. So you  should regular SemVer tags in your root
composer.json file as long as you:

1. Have the official Drupal.org packagist defined
   (https://packages.drupal.org/8)
2. Namespace `lightning` with `acquia` and not `drupal` so it knows to fetch it
   from the main Packagist.

For example:

    "repositories": [
        {
            "type": "composer",
            "url": "https://packages.drupal.org/8"
        }
    ],
    "require": {
        "acquia/lightning": "~2.0.0"
    },

If you used the update script provided in Lightning 1.14 to update to 2.0.0,
your root composer.json file should have been automatically converted for you.
