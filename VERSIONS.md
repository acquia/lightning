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
| __x__ | Works with any minor version of the indicated Major version of Drupal core |
| __1__ | Major version of Lightning |
| __2__ | Feature release of Lightning |
| __3__ | Sprint release between feature releases |

Lightning typically makes a sprint release every four weeks. We'll also use
sprint releases to package new releases of Drupal Core with Lightning as they
become available.

Lightning release tags are synchronized with releases of Lightning's [Composer-based installer](https://github.com/acquia/lightning-project).
Since the installer is not distributed on drupal.org, Lightning version 8.x-1.23
corresponds with installer version 8.1.2-patch3, and so on.
