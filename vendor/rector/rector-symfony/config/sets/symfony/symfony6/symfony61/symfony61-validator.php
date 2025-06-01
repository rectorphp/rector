<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony61\Rector\StaticPropertyFetch\ErrorNamesPropertyToConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ErrorNamesPropertyToConstantRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/45623
        'Symfony\\Component\\Validator\\Constraints\\ExpressionLanguageSyntax' => 'Symfony\\Component\\Validator\\Constraints\\ExpressionSyntax',
        'Symfony\\Component\\Validator\\Constraints\\ExpressionLanguageSyntaxValidator' => 'Symfony\\Component\\Validator\\Constraints\\ExpressionSyntaxValidator',
    ]);
};
