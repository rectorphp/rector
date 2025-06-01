<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Symfony\Symfony62\Rector\MethodCall\SimplifyFormRenderingRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(SimplifyFormRenderingRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/46854
        new MethodCallRename('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController', 'renderForm', 'render'),
    ]);
};
