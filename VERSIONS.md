## Lightning Release Tags
Lightning uses a three-part semantic versioning nomenclature within the
constraints of drupal.org's current capabilities. See
[this drupal.org issue](https://www.drupal.org/node/1612910) for the latest.

The three parts of our versioning system are MAJOR.MINOR.PATCH. However, due
to the current constraints of drupal.org, there is no separator between the
FEATURE and SPRINT digits.

Given the following tag: 8.x-1.234:

|       |                              |
|-------|------------------------------|
| __8__ | Major version of Drupal Core |
| __x__ |  |
| __1__ | Major version of Lightning |
| __2__ | Minor release of Lightning. Also increments with minor core releases. |
| __34__ | Patch release of Lightning. |

Lightning typically makes a patch release every four weeks. New minor releases
of Drupal Core will be packaged as minor releases of Lightning. As such,
Lightning minor versions are tied to minor versions of Drupal core.

Lightning also has proper [SemVer tags](http://semver.org/) which are commonly
used to refer to releases and should be used when constraining Lightning in your
root composer.json file. Because of the limitations of Drupal.Org's tags, the
minor release number in Lightning will always be one digit (that is, nine or
smaller).

Lightning provides a Console command that returns a proper SemVer version number
for the version of Lightning you have installed. Your root composer.json file
should include something similar to the following:

    "repositories": [
        {
            "type": "composer",
            "url": "https://packages.drupal.org/8"
        }
    ],
    "require": {
        "acquia/lightning": "~3.0.0"
    },
