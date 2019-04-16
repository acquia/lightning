Failures to send telemetry are intended to be graceful and quiet. Exceptions are caught and output is intentionally suppressed.

To disable error suppression, set the environmental variable ACQUIA_TELEMETRY_LOUD to a value that evaluates as true.
E.g., `ACQUIA_TELEMETRY_LOUD=1 drush cron`.
