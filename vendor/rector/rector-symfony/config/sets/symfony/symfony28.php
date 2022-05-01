<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Symfony\Rector\StaticCall\ParseFileRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Symfony\Rector\StaticCall\ParseFileRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector::class, [
        // https://github.com/symfony/symfony/commit/912fc4de8fd6de1e5397be4a94d39091423e5188
        new \Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue('Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface', 'generate', 2, \true, 'Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface::ABSOLUTE_URL'),
        new \Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue('Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface', 'generate', 2, \false, 'Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface::ABSOLUTE_PATH'),
        new \Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue('Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface', 'generate', 2, 'relative', 'Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface::RELATIVE_PATH'),
        new \Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue('Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface', 'generate', 2, 'network', 'Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface::NETWORK_PATH'),
    ]);
};
