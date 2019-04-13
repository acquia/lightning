Failures to send Lightning Telemetry are intended to be graceful and quiet. Exceptions are caught and output is intentionally suppressed.

To disable error suppression, set the environmental variable LIGHTNING_TELEMETRY_LOUD to a value that evaluates as true.
E.g., `LIGHTNING_TELEMETRY_LOUD=1 drush cron`.