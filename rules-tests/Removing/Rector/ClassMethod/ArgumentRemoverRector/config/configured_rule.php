<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\Tests\Removing\Rector\ClassMethod\ArgumentRemoverRector\Source\Persister;
use Rector\Tests\Removing\Rector\ClassMethod\ArgumentRemoverRector\Source\RemoveInTheMiddle;
use Symfony\Component\Yaml\Yaml;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(ArgumentRemoverRector::class, [
            new ArgumentRemover(Persister::class, 'getSelectJoinColumnSQL', 4, null), new ArgumentRemover(
                Yaml::class,
                'parse',
                1,
                ['Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS', 'hey', 55, 5.5]
            ),
            new ArgumentRemover(RemoveInTheMiddle::class, 'run', 1, [
                'name' => 'second',
            ]),
        ]);
};
