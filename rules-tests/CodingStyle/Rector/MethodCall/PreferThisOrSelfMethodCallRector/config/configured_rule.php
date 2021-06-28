<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Rector\CodingStyle\Enum\PreferenceSelfThis;
use Rector\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\AbstractTestCase;
use Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(PreferThisOrSelfMethodCallRector::class)
        ->call('configure', [[
            PreferThisOrSelfMethodCallRector::TYPE_TO_PREFERENCE => [
                AbstractTestCase::class => ValueObjectInliner::inline(PreferenceSelfThis::PREFER_SELF()),
                BeLocalClass::class => ValueObjectInliner::inline(PreferenceSelfThis::PREFER_THIS()),
                TestCase::class => ValueObjectInliner::inline(PreferenceSelfThis::PREFER_SELF()),
            ],
        ]]);
};
