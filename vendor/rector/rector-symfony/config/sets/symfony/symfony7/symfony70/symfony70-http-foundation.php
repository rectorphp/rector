<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#httpfoundation
        'Symfony\\Component\\HttpFoundation\\RequestMatcher' => 'Symfony\\Component\\HttpFoundation\\ChainRequestMatcher',
        'Symfony\\Component\\HttpFoundation\\ExpressionRequestMatcher' => 'Symfony\\Component\\HttpFoundation\\RequestMatcher\\ExpressionRequestMatcher',
    ]);
    // @see https://github.com/symfony/symfony/pull/50826
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\\Component\\HttpFoundation\\Request', 'getContentType', 'getContentTypeFormat')]);
};
