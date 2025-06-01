<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\ConvertRenderTemplateShortNotationToBundleSyntaxRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertIsSuccessfulRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertResponseCodeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // assets deprecation
        'Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\AssetsHelper' => 'Symfony\\Component\\Asset\\Packages',
        // templating
        'Symfony\\Bundle\\FrameworkBundle\\Templating\\EngineInterface' => 'Symfony\\Component\\Templating\\EngineInterface',
    ]);
    $rectorConfig->rules([
        ConvertRenderTemplateShortNotationToBundleSyntaxRector::class,
        # https://symfony.com/blog/new-in-symfony-4-3-better-test-assertions
        //
        WebTestCaseAssertIsSuccessfulRector::class,
        WebTestCaseAssertResponseCodeRector::class,
    ]);
};
