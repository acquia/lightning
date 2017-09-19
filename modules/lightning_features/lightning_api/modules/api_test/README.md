# API Test

API Test is a hidden module that provides an OAuth2 Client suitable for testing.

## Client Details

* **Client ID**: api_test-oauth2-client
* **Scope**: Basic page creator role
* **Client Secret**: oursecret
* **User**: api-test-user
* **Password**: admin

## Example Usage

You can receive an access token for this User and Client using the following:

```
curl -X POST -d "grant_type=password&client_id=api_test-oauth2-client&client_secret=oursecret&username=api-test-user&password=admin" [YOURDOMAIN]/oauth/token
```

See ApiTest.php for more examples. This module is for testing and as an example
only. Do not use this module's configuration as a starting point for your own
configuration.
