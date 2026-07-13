<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony43\Rector\MethodCall\ConvertRenderTemplateShortNotationToBundleSyntaxRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // assets deprecation
        'Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper' => 'Symfony\Component\Asset\Packages',
        // templating
        'Symfony\Bundle\FrameworkBundle\Templating\EngineInterface' => 'Symfony\Component\Templating\EngineInterface',
    ]);
    $rectorConfig->rules([ConvertRenderTemplateShortNotationToBundleSyntaxRector::class]);
};
