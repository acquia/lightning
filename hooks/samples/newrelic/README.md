# Example of Acquia Cloud Hook to notify New Relic API of code version deployments

Installation Steps (assumes New Relic subscription setup and Acquia Cloud Hooks installed in repo):

* Login to New Relic and goto https://rpm.newrelic.com/accounts/(UserID)/applications/(ApplicationID)/deployments/instructions
* From the instructions get your application_id and your x-api-key. Store these variables and a username you wish to send to New Relic in $HOME/newrelic_settings file on your Acquia Cloud Server (see example file).
* Set the execution bit to on i.e. chmod a+x newrelic_settings
* Add newrelic.sh to dev, test, prod or common post-code-deploy hook.


