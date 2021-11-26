# Contribute to Symfony Health Check Bundle

Thank you for contributing!

Before we can merge your Pull-Request here are some guidelines that you need to follow.
These guidelines exist not to annoy you, but to keep the code base clean,
unified and future proof.

## Dependencies

We're using [`composer/composer`](https://github.com/composer/composer) to manage dependencies

## Coding Standard

This project uses [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) to enforce coding standards.
The coding standard rules are defined in the **phpcs.xml.dist** file (part of this repository).

Your Pull-Request must be compliant with the said standard.
To check your code you can run `composer run cs`. This command will give you a list of violations in your code (if any).

The most common errors can be automatically fixed just by running `composer run cs-fix`.

[coding standard homepage]: https://github.com/doctrine/coding-standard

## Static analysing tools

This project uses [PHPStan](https://github.com/phpstan/phpstan) to find errors in code without running it.
The analyser configuration is defined in the **phpstan.neon.dist** file (part of this repository).

Your Pull-Request must be compliant with PHPStan rules.
To check your code you can run `composer run phpstan`. This command will give you a list of violations in your code (if any).

If error can't be fixed you should add it to `ignoreErrors` in  **phpstan.neon.dist**

## Unit-Tests

Please try to add a test for your pull-request. This project uses PHPUnit as testing framework.

You can run the unit-tests by calling `composer run phpunit`.

New features without tests can't be merged.

## Conventional Commits specification

We are using husky pre-commit hook to check commit naming compliance with Conventional Commits convention.
You have to run `npm install` after cloning project and then all commit naming errors (if any) will be shown in console.
It helps us to create explicit commit history and automate release flow. 

## Issues and Bugs

To create a new issue, you can use the GitHub issue tracking system.

## Getting merged

Please allow us time to review your pull requests. We will give our best to review
everything as fast as possible, but cannot always live up to our own expectations.

Pull requests without tests most probably will not be merged.
Documentation PRs obviously do not require tests.

Thank you very much again for your contribution!
