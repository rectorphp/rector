<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony73\Rector\Class_\AddVoteArgumentToVoteOnAttributeRector;
use Rector\Symfony\Symfony73\Rector\Class_\RemoveEraseCredentialsRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AddVoteArgumentToVoteOnAttributeRector::class, RemoveEraseCredentialsRector::class]);
};
