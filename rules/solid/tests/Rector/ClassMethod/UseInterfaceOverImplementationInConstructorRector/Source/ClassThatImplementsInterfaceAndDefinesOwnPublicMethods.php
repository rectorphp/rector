<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source;

final class ClassThatImplementsInterfaceAndDefinesOwnPublicMethods implements InterfaceFour
{
    public function foo(): string
    {
        return 'bar';
    }

    public function methodFromInterface(): void
    {
    }
}
