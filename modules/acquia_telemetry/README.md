# Acquia Telemetry
This module sends anonymous data about Acquia product usage to Acquia
for product development purposes.

No private information will be gathered. Data will **not** be used for
marketing or sold to any third parties.

Telemetry is opt-in only and can be disabled at any time by uninstalling
the acquia_telemetry module.

## Contribution Guide
Failures to send telemetry are intended to be graceful and quiet. Exceptions
are caught and output is intentionally suppressed.

To disable error suppression, set the the state key `acquia_telemetry.loud` to
TRUE, e.g., `drush state-set acquia_telemetry.loud true`.
