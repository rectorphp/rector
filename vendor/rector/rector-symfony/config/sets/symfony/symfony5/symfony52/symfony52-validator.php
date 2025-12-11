<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony52\Rector\MethodCall\ValidatorBuilderEnableAnnotationMappingRector;
use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#validator
        ValidatorBuilderEnableAnnotationMappingRector::class,
    ]);
    // Symfony\Component\Validator\Constraints\Email::VALIDATION_MODE_* added in Symfony 4.1
    // When using PHP 8 attributes (available from Symfony 5.2 set), map string modes to constants
    $rectorConfig->ruleWithConfiguration(AttributeKeyToClassConstFetchRector::class, [new AttributeKeyToClassConstFetch('Symfony\Component\Validator\Constraints\Email', 'mode', 'Symfony\Component\Validator\Constraints\Email', ['strict' => 'VALIDATION_MODE_STRICT', 'loose' => 'VALIDATION_MODE_LOOSE', 'html5' => 'VALIDATION_MODE_HTML5'])]);
};
