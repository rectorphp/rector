<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy;
use Rector\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector;
use Rector\Transform\ValueObject\ReplaceParentCallByPropertyCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ReplaceParentCallByPropertyCallRector::class)
        ->configure([
            new ReplaceParentCallByPropertyCall(TypeClassToReplaceMethodCallBy::class, 'someMethod', 'someProperty'),
        ]);
};
