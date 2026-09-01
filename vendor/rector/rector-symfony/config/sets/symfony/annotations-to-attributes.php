<?php

declare (strict_types=1);
namespace RectorPrefix202609;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
// @see https://symfony.com/blog/new-in-symfony-5-2-constraints-as-php-attributes
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([__DIR__ . '/symfony5/symfony52-validator-attributes.php']);
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // @see https://symfony.com/blog/new-in-symfony-5-2-php-8-attributes
        new AnnotationToAttribute('required', 'Symfony\Contracts\Service\Attribute\Required'),
        new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route'),
        // @see Symfony 5.2+ https://github.com/symfony/doctrine-bridge/commit/02d2cf4743331e6b69ffd1d68e09b7e2dc417201#diff-1a16e2739e51eab000116d0542bd0226cea59a6d64711740ed7ce14769f95d1b
        new AnnotationToAttribute('Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity'),
    ]);
};
