<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\Transform\Rector\New_\NewToStaticCallRector\Source\FromNewClass;
use Rector\Tests\Transform\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass;
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\ValueObject\NewToStaticCall;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(NewToStaticCallRector::class)
        ->configure([new NewToStaticCall(FromNewClass::class, IntoStaticClass::class, 'run')]);
};
