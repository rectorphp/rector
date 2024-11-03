<?php

declare (strict_types=1);
namespace RectorPrefix202411;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Renaming\Rector\Class_\RenameAttributeRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameAttribute;
// @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md
return static function (RectorConfig $rectorConfig) : void {
    // @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#dependencyinjection
    $rectorConfig->ruleWithConfiguration(RenameAttributeRector::class, [new RenameAttribute('Symfony\\Component\\DependencyInjection\\Attribute\\MapDecorated', 'Symfony\\Component\\DependencyInjection\\Attribute\\AutowireDecorated')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#frameworkbundle
        'Symfony\\Component\\Serializer\\Normalizer\\ObjectNormalizer' => 'Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface',
        'Symfony\\Component\\Serializer\\Normalizer\\PropertyNormalizer' => 'Symfony\\Component\\Serializer\\Normalizer\\NormalizerInterface',
        // @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#httpfoundation
        'Symfony\\Component\\HttpFoundation\\RequestMatcher' => 'Symfony\\Component\\HttpFoundation\\ChainRequestMatcher',
        'Symfony\\Component\\HttpFoundation\\ExpressionRequestMatcher' => 'Symfony\\Component\\HttpFoundation\\RequestMatcher\\ExpressionRequestMatcher',
    ]);
    // @see https://github.com/symfony/symfony/pull/50826
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\\Component\\HttpFoundation\\Request', 'getContentType', 'getContentTypeFormat')]);
    // the "@required" was dropped, use attribute instead
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('required', 'Symfony\\Contracts\\Service\\Attribute\\Required')]);
};
