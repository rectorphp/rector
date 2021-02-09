<?php

use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\Tests\Rector\ClassMethod\ArgumentRemoverRector\Source\Persister;
use Rector\Removing\Tests\Rector\ClassMethod\ArgumentRemoverRector\Source\RemoveInTheMiddle;
use Rector\Removing\ValueObject\ArgumentRemover;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Yaml\Yaml;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ArgumentRemoverRector::class)->call('configure', [[
        ArgumentRemoverRector::REMOVED_ARGUMENTS => ValueObjectInliner::inline([

            new ArgumentRemover(Persister::class, 'getSelectJoinColumnSQL', 4, null), new ArgumentRemover(
                Yaml::class,
                'parse',
                1,
                [
                    'Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS',
                    'hey',
                    55,
                    5.5,

                ]), new ArgumentRemover(RemoveInTheMiddle::class, 'run', 1, [
                    'name' => 'second',
                ]), ]
        ),
    ]]);
};
