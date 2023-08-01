<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony61\Rector\Class_\CommandPropertyToAttributeRector;
use Rector\Symfony\Symfony61\Rector\Class_\MagicClosureTwigExtensionToNativeMethodsRector;
use Rector\Symfony\Symfony61\Rector\StaticPropertyFetch\ErrorNamesPropertyToConstantRector;
# https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.1.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([CommandPropertyToAttributeRector::class, ErrorNamesPropertyToConstantRector::class, MagicClosureTwigExtensionToNativeMethodsRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/43982
        'Symfony\\Component\\Serializer\\Normalizer\\ContextAwareDenormalizerInterface' => 'Symfony\\Component\\Serializer\\Normalizer\\DenormalizerInterface',
        'Symfony\\Component\\Serializer\\Normalizer\\ContextAwareNormalizerInterface' => 'Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface',
        // @see https://github.com/symfony/symfony/pull/45623
        'Symfony\\Component\\Validator\\Constraints\\ExpressionLanguageSyntax' => 'Symfony\\Component\\Validator\\Constraints\\ExpressionSyntax',
        'Symfony\\Component\\Validator\\Constraints\\ExpressionLanguageSyntaxValidator' => 'Symfony\\Component\\Validator\\Constraints\\ExpressionSyntaxValidator',
    ]);
};
