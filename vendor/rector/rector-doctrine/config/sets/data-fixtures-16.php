<?php

declare (strict_types=1);
namespace RectorPrefix202507;

use Rector\Config\RectorConfig;
use Rector\Doctrine\DoctrineFixture\Rector\MethodCall\AddGetReferenceTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([AddGetReferenceTypeRector::class]);
};
