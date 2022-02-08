Upgrade Symfony Health Check Bundle V1.0.0
=================================

Step 1: Update the Symfony Health Check Bundle via Composer
----------------------------------
```console
$ "macpaw/symfony-health-check-bundle": "^v1.0.0"
```

### Next, use Composer to download new versions of the libraries:
```console
$ composer update "macpaw/symfony-health-check-bundle"
```

###Dependency Errors

If you get a dependency error, it may mean that you also need to upgrade other libraries that are dependencies of the libraries. To allow that, pass the --with-all-dependencies flag:
```console
$ composer update "macpaw/symfony-health-check-bundle" -with-all-dependencies
```

Step 2: Update the Symfony Health Check Bundle via Composer
----------------------------------

## Automatical

Over time - and especially when you upgrade to a new version of a library - an updated version of the recipe may be available. These updates are usually minor - e.g. new comments in a configuration file - but it's a good idea to keep your files in sync with the recipes.

Symfony Flex provides several commands to help upgrade your recipes. Be sure to commit any unrelated changes you're working on before starting:

```console
$ composer recipes


$ composer recipes symfony/framework-bundle


$ composer recipes:install symfony/framework-bundle --force -v
```

The tricky part of this process is that the recipe "update" does not perform any intelligent "upgrading" of your code. Instead, the updates process re-installs the latest version of the recipe which means that your custom code will be overridden completely. After updating a recipe, you need to carefully choose which changes you want, and undo the rest.

## Manual:

### Old Config:
`config/packages/symfony_health_check.yaml`
```yaml
symfony_health_check:
    health_checks:
        - id: symfony_health_check.doctrine_check
```

### New Config:
```yaml
symfony_health_check:
    health_checks:
        - id: symfony_health_check.doctrine_check
    ping_checks:
        - id: symfony_health_check.status_up_check
```

Security Optional:
----------------------------------
`config/packages/security.yaml`

### Old Config:
```yaml
    firewalls:
        healthcheck:
            pattern: ^/health
            security: false
```

### New Config:
```yaml
    firewalls:
        healthcheck:
            pattern: ^/health
            security: false
        ping:
            pattern: ^/ping
            security: false
```

Step 3: Update Custom Health Check
----------------------------------
We need change return type array -> Response class


### Old:
```
use SymfonyHealthCheckBundle\Dto\Response;

class StatusUpCheck implements CheckInterface
{
    public function check(): array
    {
        return ['status' => 'up'];
    }
}
```

### New:
```
use SymfonyHealthCheckBundle\Dto\Response;

class StatusUpCheck implements CheckInterface
{
    public function check(): Response
    {
        return new Response('status', true, 'up');
    }
}
```

Step 4: Remove Custom Error library
----------------------------------
Remove custom error in handler https://github.com/MacPaw/symfony-health-check-bundle/tree/v0.8.0/src/Exception 
