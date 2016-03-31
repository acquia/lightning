## Lightning Release Tags

Lightning uses a three-part semantic versioning nomenclature within the
constraints of Drupal.org's current capabilities. See
"[Switch to Semantic Versioning for Drupal contrib extensions ][d.o_semver]" for
the latest information regarding this issue on drupal.org.

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

Lightning typically makes a Sprint release every four weeks. We'll also use
sprint releases to package new releases of Drupal Core with Lightning as they
become available.

Lightning release tags are synchronized with releases of the [Lightning Project Composer Package][lightning_project].
Since the Lightning Project is not under the constraints of drupal.org, the
Lightning tag 8.x-1.23 corresponds to the Lightning Project tag 8.1.2-patch3.

