<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony63\Rector\Class_\ParamAndEnvAttributeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // @see https://symfony.com/blog/new-in-symfony-6-3-dependency-injection-improvements#new-options-for-autowire-attribute
        ParamAndEnvAttributeRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/commit/b653adf426aedc66d16c5fc1cf71e261f20b9638
        'Symfony\\Component\\DependencyInjection\\Attribute\\MapDecorated' => 'Symfony\\Component\\DependencyInjection\\Attribute\\AutowireDecorated',
    ]);
};
