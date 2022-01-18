Symfony Health Check Bundle
=================================

| Version | Build Status | Code Coverage |
|:---------:|:-------------:|:-----:|
| `master`| [![CI][master Build Status Image]][master Build Status] | [![Coverage Status][master Code Coverage Image]][master Code Coverage] |
| `develop`| [![CI][develop Build Status Image]][develop Build Status] | [![Coverage Status][develop Code Coverage Image]][develop Code Coverage] |

Installation
============

Step 1: Download the Bundle
----------------------------------
Open a command console, enter your project directory and execute:

###  Applications that use Symfony Flex

```console
$ composer require macpaw/symfony-health-check-bundle
```

### Applications that don't use Symfony Flex

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require macpaw/symfony-health-check-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
----------------------------------
Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            SymfonyHealthCheckBundle\SymfonyHealthCheckBundle::class => ['all' => true],
        );

        // ...
    }

    // ...
}
```

Create Symfony Health Check Bundle Config:
----------------------------------
`config/packages/symfony_health_check.yaml`

Configurating health check - all available you can see [here](https://github.com/MacPaw/symfony-health-check-bundle/tree/master/src/Check).

```yaml
symfony_health_check:
    health_checks:
        - id: symfony_health_check.doctrine_check
    ping_checks:
        - id: symfony_health_check.status_up_check
```

Create Symfony Health Check Bundle Routing Config:
----------------------------------
`config/routes/symfony_health_check.yaml`

```yaml
health_check:
    resource: '@SymfonyHealthCheckBundle/Resources/config/routes.xml'
```

Step 3: Configuration
=============

Security Optional:
----------------------------------
`config/packages/security.yaml`

If you are using [symfony/security](https://symfony.com/doc/current/security.html) and your health check is to be used anonymously, add a new firewall to the configuration

```yaml
    firewalls:
        healthcheck:
            pattern: ^/health
            security: false
        ping:
            pattern: ^/ping
            security: false
```

Step 4: Additional settings
=============

Add Custom Check:
----------------------------------
It is possible to add your custom health check:

```php
<?php

declare(strict_types=1);

namespace YourProject\Check;

use SymfonyHealthCheckBundle\Dto\Response;

class CustomCheck implements CheckInterface
{
    public function check(): Response
    {
        return new Response('status', true, 'up');
    }
}
```

Then we add our custom health check to collection

```yaml
symfony_health_check:
    health_checks:
        - id: symfony_health_check.doctrine_check
        - id: custom_health_check // custom service check id
```

How Change Route:
----------------------------------
You can change the default behavior with a light configuration, remember to return to Step 3 after that:
```yaml
health:
    path: /your/custom/url
    methods: GET
    controller: SymfonyHealthCheckBundle\Controller\HealthController::healthCheckAction
    
ping:
    path: /your/custom/url
    methods: GET
    controller: SymfonyHealthCheckBundle\Controller\PingController::pingAction

```

[master Build Status]: https://github.com/macpaw/symfony-health-check-bundle/actions?query=workflow%3ACI+branch%3Amaster
[master Build Status Image]: https://github.com/macpaw/symfony-health-check-bundle/workflows/CI/badge.svg?branch=master
[develop Build Status]: https://github.com/macpaw/symfony-health-check-bundle/actions?query=workflow%3ACI+branch%3Adevelop
[develop Build Status Image]: https://github.com/macpaw/symfony-health-check-bundle/workflows/CI/badge.svg?branch=develop
[master Code Coverage]: https://codecov.io/gh/macpaw/symfony-health-check-bundle/branch/master
[master Code Coverage Image]: https://img.shields.io/codecov/c/github/macpaw/symfony-health-check-bundle/master?logo=codecov
[develop Code Coverage]: https://codecov.io/gh/macpaw/symfony-health-check-bundle/branch/develop
[develop Code Coverage Image]: https://img.shields.io/codecov/c/github/macpaw/symfony-health-check-bundle/develop?logo=codecov
