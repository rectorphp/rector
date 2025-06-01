<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://symfony.com/blog/new-in-symfony-5-4-nested-validation-attributes
    // @see https://github.com/symfony/symfony/pull/41994
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Symfony\\Component\\Validator\\Constraints\\All'), new AnnotationToAttribute('Symfony\\Component\\Validator\\Constraints\\Collection'), new AnnotationToAttribute('Symfony\\Component\\Validator\\Constraints\\AtLeastOneOf'), new AnnotationToAttribute('Symfony\\Component\\Validator\\Constraints\\Sequentially')]);
};
