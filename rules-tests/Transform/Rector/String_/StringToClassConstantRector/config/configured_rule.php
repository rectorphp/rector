<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use Rector\Transform\ValueObject\StringToClassConstant;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(StringToClassConstantRector::class)
        ->configure([

            new StringToClassConstant('compiler.post_dump', 'Yet\AnotherClass', 'CONSTANT'),
            new StringToClassConstant('compiler.to_class', 'Yet\AnotherClass', 'class'),

        ]);
};
