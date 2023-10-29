# 📐 ArchCheck 
[![Software License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![GitLab tag (latest by SemVer)](https://img.shields.io/gitlab/v/tag/hgraca/php-extension?label=version&sort=semver&color=blue)](https://gitlab.com/modulith/arch-check/-/tags)

[![build status](https://gitlab.com/modulith/arch-check/badges/master/pipeline.svg?ignore_skipped=true)](https://gitlab.com/modulith/arch-check/-/pipelines)
[![coverage report](https://gitlab.com/modulith/arch-check/badges/master/coverage.svg)](https://gitlab.com/modulith/arch-check/commits/master)

# Index

1. [Introduction](#introduction)
1. [Installation](#installation)
1. [Usage](#usage)
1. [Available rules](#available-rules)
1. [Rule Builders](#rule-builders)
1. [Credits](#credits)

# Introduction

Archcheck helps you to keep your PHP codebase coherent and solid, by permitting to add some architectural constraint check to your workflow.
You can express the constraint that you want to enforce, in simple and readable PHP code, for example:

```php
Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
    ->should(new HaveNameMatching('*Controller'))
    ->because('it\'s a symfony naming convention');
```

# Installation

## Using Composer

```bash
composer require --dev archcheck/archcheck
```

# Usage

To use this tool you need to launch a command via Bash:

```
archcheck check
```

With this command `archcheck` will search all rules in the root of your project the default config file called `archcheck.php`.
You can also specify your configuration file using `--config` option like this:

```
archcheck check --config=/project/yourConfigFile.php
```

By default, a progress bar will show the status of the ongoing analysis.

### Using a baseline file

If there are a lot of violations in your codebase and you can't fix them now, 
you can use the baseline feature to instruct the tool to ignore past violations.

To create a baseline file, run the `check` command with the `generate-baseline` parameter as follows:

```
archcheck check --generate-baseline
```
This will create a `archcheck-baseline.json`, if you want a different file name you can do it with:
```
archcheck check --generate-baseline=my-baseline.json
```

It will produce a json file with the current list of violations.  

If is present a baseline file with the default name will be used automatically.

To use a different baseline file, run the `check` command with the `use-baseline` parameter as follows:

```
archcheck check --use-baseline=my-baseline.json
```

To avoid using the default baseline file, you can use the `skip-baseline` option:

```
archcheck check --skip-baseline
```

### Line numbers in baseline

By default, the baseline check also looks at line numbers of known violations.
When a line before the offending line changes, the line numbers change and the check fails despite the baseline.

With the optional flag `ignore-baseline-linenumbers`, you can ignore the line numbers of violations:

```
archcheck check --ignore-baseline-linenumbers
```

*Warning*: When ignoring line numbers, archcheck can no longer discover if a rule is violated additional times in the same file.

## Configuration

Example of configuration file `archcheck.php`

```php
<?php
declare(strict_types=1);

use Modulith\ArchCheck\ClassSet;
use Modulith\ArchCheck\CLI\Config;
use Modulith\ArchCheck\Expression\ForClasses\HaveNameMatching;
use Modulith\ArchCheck\Expression\ForClasses\NotHaveDependencyOutsideNamespace;
use Modulith\ArchCheck\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Modulith\ArchCheck\Rules\Rule;

return static function (Config $config): void {
    $mvcClassSet = ClassSet::fromDir(__DIR__.'/mvc');

    $rules = [];

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
        ->should(new HaveNameMatching('*Controller'))
        ->because('we want uniform naming');

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Domain'))
        ->should(new NotHaveDependencyOutsideNamespace('App\Domain'))
        ->because('we want protect our domain');

    $config
        ->add($mvcClassSet, ...$rules);
};
```
Archcheck can detect violations also on DocBlocks custom annotations (like `@Assert\NotBlank` or `@Serializer\Expose`).
If you want to disable this feature you can add this simple configuration:
```php
$config->skipParsingCustomAnnotations();
```

# Available rules

**Hint**: If you want to test how a Rule work, you can use the command like `archcheck debug:expression <RuleName> <arguments>` to check which class satisfy the rule in your current folder.

For example: `archcheck debug:expression ResideInOneOfTheseNamespaces App`

---

Currently, you can check if a class:

### Is referenced in a given map

This is useful, for example, to ensure that DTOs like commands and
events are always set in a map, so that we are sure a serializer knows how
to serialize/deserialize them.

```php
$map = [
    'a' => 'App\Core\Component\MyComponent\Command\MyCommand',
    'b' => 'App\Core\Component\MyComponent\Event\MyEvent',
];
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Core\Component\**\Command', 'App\Core\Component\**\Event'))
    ->should(new IsMapped($map))
    ->because('we want to ensure our serializer can serialize/deserialize all commands and events');
```

### Has a corresponding code unit in another namespace

This will allow us to ensure that certain classes always have a test,
or that every test has a matching class and their namespaces are correct.

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Core\Component\**\Command\*'))
    ->should(new HaveCorrespondingUnit(
            // This will assert that class `App\Core\Component\MyComponent\Command\MyCommand`
            // has a test class in `Tests\App\Core\Component\MyComponent\Command\MyCommandTest`
            function ($fqcn) {
                return 'Tests\\'.$fqcn.'Test';
            }
        )
    )
    ->because('we want all our command handlers to have a test');
```

### Depends on a namespace

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Domain'))
    ->should(new DependsOnlyOnTheseNamespaces('App\Domain', 'Ramsey\Uuid'))
    ->because('we want to protect our domain from external dependencies except for Ramsey\Uuid');
```

### Doc block contains a string

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Domain\Events'))
    ->should(new ContainDocBlockLike('@psalm-immutable'))
    ->because('we want to enforce immutability');
```

### Doc block not contains a string

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
    ->should(new NotContainDocBlockLike('@psalm-immutable'))
    ->because('we don\'t want to enforce immutability');
```

### Extend another class

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
    ->should(new Extend('App\Controller\AbstractController'))
    ->because('we want to be sure that all controllers extend AbstractController');
```

### Has an attribute (requires PHP >= 8.0)

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
    ->should(new HaveAttribute('AsController'))
    ->because('it configures the service container');
```

### Have a name matching a pattern

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Service'))
    ->should(new HaveNameMatching('*Service'))
    ->because('we want uniform naming for services');
```

### Implements an interface

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
    ->should(new Implement('ContainerAwareInterface'))
    ->because('all controllers should be container aware');
```

### Not implements an interface

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Infrastructure\RestApi\Public'))
    ->should(new NotImplement('ContainerAwareInterface'))
    ->because('all public controllers should not be container aware');
```

### Is abstract

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Customer\Service'))
    ->should(new IsAbstract())
    ->because('we want to be sure that classes are abstract in a specific namespace');
```

### Is trait

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Customer\Service\Traits'))
    ->should(new IsTrait())
    ->because('we want to be sure that there are only traits in a specific namespace');
```

### Is final

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Domain\Aggregates'))
    ->should(new IsFinal())
    ->because('we want to be sure that aggregates are final classes');
```

### Is interface

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Interfaces'))
    ->should(new IsInterface())
    ->because('we want to be sure that all interfaces are in one directory');
```

### Is enum

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Enum'))
    ->should(new IsEnum())
    ->because('we want to be sure that all classes are enum');
```

### Is not abstract

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Domain'))
    ->should(new IsNotAbstract())
    ->because('we want to avoid abstract classes into our domain');
```

### Is not trait

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Domain'))
    ->should(new IsNotTrait())
    ->because('we want to avoid traits in our codebase');
```

### Is not final

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Infrastructure\Doctrine'))
    ->should(new IsNotFinal())
    ->because('we want to be sure that our adapters are not final classes');
```

### Is not interface

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('Tests\Integration'))
    ->should(new IsNotInterface())
    ->because('we want to be sure that we do not have interfaces in tests');
```

### Is not enum

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
    ->should(new IsNotEnum())
    ->because('we want to be sure that all classes are not enum');
```

### Not depends on a namespace

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Application'))
    ->should(new NotDependsOnTheseNamespaces('App\Infrastructure'))
    ->because('we want to avoid coupling between application layer and infrastructure layer');
```

### Not extend another class

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Controller\Admin'))
    ->should(new NotExtend('App\Controller\AbstractController'))
    ->because('we want to be sure that all admin controllers not extend AbstractController for security reasons');
```

### Don't have dependency outside a namespace

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App\Domain'))
    ->should(new NotHaveDependencyOutsideNamespace('App\Domain', ['Ramsey\Uuid']))
    ->because('we want protect our domain except for Ramsey\Uuid');
```

### Not have a name matching a pattern

```php
$rules = Rule::allClasses()
    ->that(new ResideInOneOfTheseNamespaces('App'))
    ->should(new NotHaveNameMatching('*Manager'))
    ->because('*Manager is too vague in naming classes');
```

### Reside in a namespace

```php
$rules = Rule::allClasses()
    ->that(new HaveNameMatching('*Handler'))
    ->should(new ResideInOneOfTheseNamespaces('App\Application'))
    ->because('we want to be sure that all CommandHandlers are in a specific namespace');
```


### Not reside in a namespace

```php
$rules = Rule::allClasses()
    ->that(new Extend('App\Domain\Event'))
    ->should(new NotResideInOneOfTheseNamespaces('App\Application', 'App\Infrastructure'))
    ->because('we want to be sure that all events not reside in wrong layers');
```

You can also define components and ensure that a component:
- should not depend on any component
- may depend on specific components
- may depend on any component

# Rule Builders

Archcheck offers some builders that enable you to implement more readable rules for specific contexts. 

### Component Architecture Rule Builder

Thanks to this builder you can define components and enforce dependency constraints between them in a more readable fashion.

```php
<?php

declare(strict_types=1);

use Modulith\ArchCheck\ClassSet;
use Modulith\ArchCheck\CLI\Config;
use Modulith\ArchCheck\Expression\ForClasses\HaveNameMatching;
use Modulith\ArchCheck\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Modulith\ArchCheck\RuleBuilders\Architecture\Architecture;
use Modulith\ArchCheck\Rules\Rule;

return static function (Config $config): void {
    $classSet = ClassSet::fromDir(__DIR__.'/src');

    $layeredArchitectureRules = Architecture::withComponents()
        ->component('Controller')->definedBy('App\Controller\*')
        ->component('Service')->definedBy('App\Service\*')
        ->component('Repository')->definedBy('App\Repository\*')
        ->component('Entity')->definedBy('App\Entity\*')
        ->component('Domain')->definedBy('App\Domain\*')

        ->where('Controller')->mayDependOnComponents('Service', 'Entity')
        ->where('Service')->mayDependOnComponents('Repository', 'Entity')
        ->where('Repository')->mayDependOnComponents('Entity')
        ->where('Entity')->shouldNotDependOnAnyComponent()
        ->where('Domain')->shouldOnlyDependOnComponents('Domain')

        ->rules();
        
    // Other rule definitions...

    $config->add($classSet, $serviceNamingRule, $repositoryNamingRule, ...$layeredArchitectureRules);
};
```

### Excluding classes when parser run
If you want to exclude some classes from the parser you can use the `except` function inside your config file like this:

```php
$rules[] = Rule::allClasses()
    ->except('App\Controller\FolderController\*')
    ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
    ->should(new HaveNameMatching('*Controller'))
    ->because('we want uniform naming');
```

You can use wildcards or the exact name of a class.

## Optional parameters and options
You can add parameters when you launch the tool. At the moment you can add these parameters and options: 
* `-v` : with this option you launch ArchCheck with the verbose mode to see every parsed file
* `--config`: with this parameter, you can specify your config file instead of the default. like this:
```
archcheck check --config=/project/yourConfigFile.php
```
* `--target-php-version`: With this parameter, you can specify which PHP version should use the parser. This can be useful to debug problems and to understand if there are problems with a different PHP version.
Supported PHP versions are: 7.1, 7.2, 7.3, 7.4, 8.0, 8.1, 8.2
 * `--stop-on-failure`: With this option the process will end immediately after the first violation.

## Run only a specific rule
For some reasons, you might want to run only a specific rule, you can do it using `runOnlyThis` like this:

```php
$rules[] = Rule::allClasses()
    ->except('App\Controller\FolderController\*')
    ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
    ->should(new HaveNameMatching('*Controller'))
    ->because('we want uniform naming')
    ->runOnlyThis();
```

# Credits

> This project is a clone of [phparkitect/arkitect](https://github.com/phparkitect/arkitect).
>
> We decided to make a fork because our PRs were becoming stale,
which means that either the original project is not maintained or they
do not wish to go in the direction we need the project to go.
>
> In any case, the initial work is commendable and very appreciated.
