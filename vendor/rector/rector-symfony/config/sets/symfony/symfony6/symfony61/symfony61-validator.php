<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony61\Rector\StaticPropertyFetch\ErrorNamesPropertyToConstantRector;
use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ErrorNamesPropertyToConstantRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/45623
        'Symfony\Component\Validator\Constraints\ExpressionLanguageSyntax' => 'Symfony\Component\Validator\Constraints\ExpressionSyntax',
        'Symfony\Component\Validator\Constraints\ExpressionLanguageSyntaxValidator' => 'Symfony\Component\Validator\Constraints\ExpressionSyntaxValidator',
    ]);
    // @see https://github.com/symfony/validator/blob/6.1/Constraints/Email.php
    $rectorConfig->ruleWithConfiguration(AttributeKeyToClassConstFetchRector::class, [new AttributeKeyToClassConstFetch('Symfony\Component\Validator\Constraints\Email', 'mode', 'Symfony\Component\Validator\Constraints\Email', ['html5-allow-no-tld' => 'VALIDATION_MODE_HTML5_ALLOW_NO_TLD'])]);
};
