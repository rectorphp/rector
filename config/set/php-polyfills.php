<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Php73\Rector\BooleanOr\IsCountableRector;
use Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector;
use Rector\Php80\Rector\Identical\StrEndsWithRector;
use Rector\Php80\Rector\Identical\StrStartsWithRector;
use Rector\Php80\Rector\NotIdentical\StrContainsRector;
use Rector\Php80\Rector\Ternary\GetDebugTypeRector;
// @note longer rule registration must be used here, to separate from withRules() from root rector.php
// these rules can be used ahead of PHP version,
// as long composer.json includes particular symfony/php-polyfill package
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ArrayKeyFirstLastRector::class, IsCountableRector::class, GetDebugTypeRector::class, StrStartsWithRector::class, StrEndsWithRector::class, StrContainsRector::class]);
};
