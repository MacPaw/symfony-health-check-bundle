Symfony Health Check Bundle
=================================

Installation
============

Step 1: Download the Bundle
----------------------------------
Open a command console, enter your project directory and execute:

###  Applications that use Symfony Flex (In Progress)

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
        - id: symfony_health_check_bundle.doctrine_check
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
```

Step 4: Additional settings
=============
It is possible to add your custom health check:

```php
<?php

declare(strict_types=1);

namespace YourProject\Check;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Exception\ServiceNotFoundException;
use Throwable;

class CustomCheck implements CheckInterface
{
    private const CHECK_RESULT_KEY = 'customConnection';
    
    public function check(): array
    {
        return [self::CHECK_RESULT_KEY => true];
    }
}
```

Then we add our custom health check to collection

```yaml
symfony_health_check:
    health_checks:
        - id: symfony_health_check_bundle.doctrine_check
        - id: custom_health_check
```
