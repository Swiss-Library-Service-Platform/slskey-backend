# Developer Notes

## Table of Contents
1. [Technology Stack](#technology-stack)
2. [Local Development using Docker](#local-development-using-docker)
    - [Database Migrations](#database-migrations)
3. [Testing](#testing)
    - [Test Database](#test-database)
    - [Test Structure](#test-structure)
    - [Run Tests](#run-tests)
    - [Test Coverage](#test-coverage)
4. [Productive Deployment](#productive-deployment)
    - [Requirements](#requirements)
    - [First Deployment](#first-deployment)
    - [Deploy Changes](#deploy-changes)
        - [Frontend Changes](#frontend-changes)
        - [Backend Changes](#backend-changes)
5. [Update SAML2 Identity Provider (e.g. SWITCH edu-ID)](#update-saml2-identity-provider-eg-switch-edu-id)
    - [Two examples:](#two-examples)
        - [Switch edu-ID](#switch-edu-id)
        - [SLSP AuthProxy](#slsp-authproxy)
6. [GitHub Workflows](#github-workflows)
    - [Pull Request Workflow](#pull-request-workflow)
        - [Linting](#linting)
        - [Testing & Coverage](#testing--coverage)
7. [PHP Doc Header](#php-doc-header)


## Technology Stack
This application uses: 
- Laravel
- Inertia with VueJS Frontend
- Tailwind CSS
- Laravel Sail for Docker development environment
- MySQL database
- Pest PHP Testing Framework

## Local Development using Docker

Requires Docker running.
Requires PHP.

- Create an .env file and configure application
  - Update DB_USERNAME, it should not be 'root'
  - Update APP_ENV=dev in .env file
  - Update DB_HOST=mysql in .env file
- Install dependencies: `composer install --ignore-platform-reqs --dev`
- Startup container: `./vendor/bin/sail up`
- Install node dependencies: `./vendor/bin/sail npm install`
- Live-update of Vue files: `./vendor/bin/sail npm run watch` 

The test app will be available at `localhost:80`

### Database Migrations

- Clear the database and freshly migrate: `./vendor/bin/sail artisan migrate:refresh`
- Seed the database with initial data: `./vendor/bin/sail artisan db:seed` 

## Testing

This application uses [Pest](https://pestphp.com/) for testing.</br>
It requires XDebug to be installed and enabled.</br>

### Test Database
Pest uses a in-memory SQlite database for testing.</br>
This DB only exists during the test execution and is destroyed afterwards.</br>
The tests use Seeders in directory `database/seeders/Test` to populate the test database.</br>

### Test Structure
The tests are located in the `tests` directory.</br>
There are two kinds of tests:
- Feature Tests: Located in `tests/Feature`. These tests test the endpoints of the application.
- Integration Tests: Located in `tests/Integration`. These tests test the business logic of the application for certain use cases and workflows.
This repository does not contain Unit Tests, as the functions themselfes are not complex enough to justify them.

### Run Tests
Run following command to run tests without coverage analysis: </br>
`vendor/bin/pest --no-coverage`

<b> Please note: </b> </br>
If many tests fail, it is possible that it is because of the config.
In this case, run `php artisan config:clear` and re-run the tests.

### Test Coverage
Pest uses XDebug to generate test coverage reports.</br>
Run following command to generate test coverage report: </br>
`./vendor/bin/pest --coverage`

## Productive Deployment

### Requirements
- PHP 8.2 or higher
    - PHP PCNTL installed
- Composer
- NodeJS (Frontend build)
- Apache/Nginx Webserver
- MySQL Database

### First Deployment

Install the application by following these steps:
1. Clone the repository
2. Create a `.env` file and configure the application. The `.env.example` file can be used as a template.
3. Run `composer install --optimize-autoloader --no-dev`
4. Run `php artisan migrate`
7. Run `php artisan optimize`
5. Run `npm install`
6. Run `npm run prod`

Then install Laravel Octane:
```
php artisan octane:install
```
- Selected FrankenPHP as application server
- It automatically downloads FrankenPHP binary

Then run the Laravel Octane server with the following command:
```
php artisan octane:start
```

Use Apache or Nginx to route the requests to the Octane server.
Apache example:

```
<VirtualHost *:80>
    ServerName slskey2.swisscovery.network

    # Redirect HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} !=on
    RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]

    # ... other configurations
</VirtualHost>

<VirtualHost *:443>
    ServerName slskey2.swisscovery.network

    SSLEngine on
    SSLCertificateKeyFile <path-to-key>
    SSLCertificateFile <path-to-certificate>

    ProxyPreserveHost On
    ProxyPass / http://127.0.0.1:8000/ (with port of octane server)
    ProxyPassReverse / http://127.0.0.1:8000/ (with port of octane server)

    # ... other configurations
</VirtualHost>
```

### Deploy Changes

#### Frontend Changes
If changes were made to the frontend (.vue files), a rebuild is neccessary to deploy the new frontend to production. </br>
This also assures that clients won't have caching problems. </br>
A restart of the Octane server is not necessary to apply changes. </br>
```
npm run prod
```

#### Backend Changes
If changes were made to the backend routes or the config, use artisan commands to update the routes and config. </br>
```
php artisan route:cache
php artisan config:cache
```

Then restart the Octane server with the following command:
```
php artisan octane:start
```



## Update SAML2 Identity Provider (e.g. SWITCH edu-ID)
This application uses 24slides/laravel-saml2 package to handle SAML2 authentication.</br>
For detailed information, please refer to their [documentation](https://github.com/24Slides/laravel-saml2).

To update SAML2 Identity Provider information use artisan:

1. Get key of current SAML2 Identiy Provider:</br>
`php artisan saml2:list-tenants`

2. Delete the current SAML2 Identity Provider information. This is necessary to update the information. </br>
`php artisan saml2:delete-tenant <key-of-existing>`

3. Add the new SAML2 Identity Provider information. </br>
`php artisan saml2:create-tenant --key=<idp-key> --entityId=<entity-id> --loginUrl=<login-url> --logoutUrl=<logout-url> --x509cert="<cert>`

4. The console will prompt the credentials for the new tenant that was created. </br>
This information 

Please note: Switch edu-ID (Resource Registry) is asking for a certificate of the SP. </br>
The X509 Certificate for the SP should be created independently of the tenant creation. </br>
And the values should be entered in the .env file. `SAML2_SP_CERT_x509` and `SAML2_SP_CERT_PRIVATEKEY` should be the path to the certificate and key file. </br>

### Two examples:

Switch edu-ID

```
php artisan saml2:create-tenant --key=eduid --entityId=https://eduid.ch/idp/shibboleth --loginUrl=https://login.eduid.ch/idp/profile/SAML2/Redirect/SSO --logoutUrl=https://login.eduid.ch/idp/profile/SAML2/Redirect/SLO --x509cert="MIID6jCCAlKgAwIBAgIUQYBVlHR1BTuCRvxNID/1YViWvXswDQYJKoZIhvcNAQELBQAwEzERMA8GA1UEAxMIZWR1aWQuY2gwHhcNMjMxMTAxMTAwMDA0WhcNMzMxMjE4MTAwMDA0WjATMREwDwYDVQQDEwhlZHVpZC5jaDCCAaIwDQYJKoZIhvcNAQEBBQADggGPADCCAYoCggGBAJ7otyXaPNpqa2T4sIw3D+G9qdns5fGWYPyKkqkbSwM/xcWY86JTyG4bNaI9za1mVmX/+JZ3rNjmihHh1pNc8Y4U3sroX4YmpHme7U0o5QzgpXgtg23NyWGSTKJG4Z4LhoPXvBvcwhTE2wRbcIiiHQExXrmntq6QXafum2eSy0wuQcsdy+jiJX1shyDC29Epf1ObglfbUYS1GkfBaV9QfsKDbEBWixdALTpubcxmEHONdmNn8wrr2IPkVod+pBgLGaacRHKt/O7aw3R5FlAOw8KANJMU9MDrp7yhH3HzQPMyc5E8HNIbh284NY3etkj0rDtI8Py2DRjMkZeUdkqyCmrskwd0aUXKTOjBT0u3TD4hayuIUUx7w3Fb3MaEWY16mFVrlw5/6AsRIiFQGbxXwXV41oTxiSBGAYBoux27Db5j6H3XEseKamk5oH8z/4HqhtzCA5oMCYkwWpf2NXwJks6T378bSw0VSklQXcoBcwPjByyrZVHpV+8bOzJdbEfRHQIDAQABozYwNDATBgNVHREEDDAKgghlZHVpZC5jaDAdBgNVHQ4EFgQUUXM4hK0yc1TiLRbbgkfB6FZ6jyowDQYJKoZIhvcNAQELBQADggGBAJN1C/JIa4hfpb7HwdSGQ/fnxRV+m/V1wp5LEZKEd/P1eIjb3ETkUL4wP90a+B22ndJIF46FqmRwu1KtVufCQE1ptYJ7JqmQEg5v1rHKMzV8CYEIZm9Wlb/dto15TAs/CkQSHyzpaeg4cNpruGtiIPUB1q01UM8NEWWU9vgrvwJjj3j3/mJPN6FWtPtZDCnIojEDV3QaeXzxQhPn5UsJuCjWX2Y81AQdVjSkbKAxyy9BWIRV2Ib6FWBZzVQwbjFADZKTiIortzAVYCrJHHU/c5enI19U6Y8/+xzu97w3STiQLgvlUUhNNgmaYMKYtRWSrqW1aaSIQ4tiylNLEb0QdoG03mDZ60WUsRF87ZrlTYU+vMNj3d4dsF70mLK+0IEyKud2r/76LQg8AEF6ESX8i3n6IGG3Tt1zeBbVZ4zNtFjvzmef7CYyLsu5MP1QBGI4QLV19rBn+6JszkavM972cTuZTVZ1BU2/g/4ZSq85+XoKRmA2GH+Wega5gX63BIOnAQ=="
```



SLSP AuthProxy:

```
php artisan saml2:create-tenant --key=authproxy --entityId=https://authproxy.swisscovery.network/realms/slskey-unibas --loginUrl=https://authproxy.swisscovery.network/realms/slskey-unibas/protocol/saml --logoutUrl=https://authproxy.swisscovery.network/realms/slskey-unibas/protocol/saml --x509cert="MIICqTCCAZECBgGLkHsVfzANBgkqhkiG9w0BAQsFADAYMRYwFAYDVQQDDA1zbHNrZXktdW5pYmFzMB4XDTIzMTEwMjE0MzkyN1oXDTMzMTEwMjE0NDEwN1owGDEWMBQGA1UEAwwNc2xza2V5LXVuaWJhczCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALhN10HWC7PUCMM+uE+D2rQAPt0dO9C1k5lbYMKF2UNuTz6s81xy2FbYT/grSBt/FknPVbzmKMUMIEZl7T1f5OQ466eTd6yLXss2CsIIGTjBx4/qbJyZkgAxhiEf0HTx0asoeLJ+wgx8dTwE3cT1xFuciQKqYg8XkMSPFIxLhB8cI9DCz3znSvfS5NmeEnhDYvp/sdWWhJNbiZQn+RAethFCV6cHcCsjiZ9Xan0MIw0PZhrxNJpzvtRI1bgjAwvTVhUp5S//zsLIrHo3pSdWaEECkkiPAHsmc9izvFu3rcTbB/9jbexR8mWa2zIOa/JJHi/DB+wjYt1L54V+i5me0ZMCAwEAATANBgkqhkiG9w0BAQsFAAOCAQEARIBiet0/ntNwf1Bsl2ilpfHEhVcFHUNh+9gCBlt1GRVCf3DPE7lhEz83jApxqSDW8Xo81GGObOkvGgBN8yGOZk13hIRqRIhWGMQVilE5UsI5wXWPZ8xOuzXWyYW2kmCoYsHX+G4ziJ11zWy9F99G9kbxiFQE54lmPYSXWxTbHYjOd+PxW9LaastJ90yKuf9ZDELBd5TYg6gjTg58ilXQN7FrKLxiq6dJSfHzfP+Kjrb5mD+Sp0loq3a46niccKdedf19zUU2qiMIXP4cf6LOlVuqY5fbBHsuLHSqXK0g01MKdUuW/NZ9cPLVlLOFq1Mu/UCiCO4HBlGy4NAtTDiiAg=="
```

## GitHub Workflows

### Pull Request Workflow
There are two workflows for Pull Requests. </br>
Pull Requests are automatically checked for linting errors and tested. </br>
If one of the checks fails, the PR cannot be merged. </br>

#### Linting
The PR workflow checks the code for linting errors. </br>
It uses the `pint` for linting using the `--test` flag to ensure the check fails if there are linting errors. </br>
To fix linting errors, run `./vendor/bin/pint` locally and push the changes. </br>
See `pint.json` for configuration. </br>

#### Testing & Coverage
The PR workflow runs the tests and generates a coverage report. </br>
See above at section Testing for more information. </br>

## PHP Doc Header
Use VS Code Plugin `PHP DocBlocker` to generate PHP Doc Header. </br>