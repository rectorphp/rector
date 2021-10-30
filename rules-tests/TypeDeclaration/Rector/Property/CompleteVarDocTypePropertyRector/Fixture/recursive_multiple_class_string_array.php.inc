<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class InvokableA {}
class InvokableB {}
class InvokableC {}
class FactoryA {}
class FactoryB {}
class FactoryC {}

final class RecursiveMultipleClassStringArray
{
    public $services = [
        'invokables' => [
            InvokableA::class,
            InvokableB::class,
            InvokableC::class,
        ],
        'factories' => [
            FactoryA::class,
            FactoryB::class,
            FactoryC::class,
        ],
    ];
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

class InvokableA {}
class InvokableB {}
class InvokableC {}
class FactoryA {}
class FactoryB {}
class FactoryC {}

final class RecursiveMultipleClassStringArray
{
    /**
     * @var array<string, mixed[]>
     */
    public $services = [
        'invokables' => [
            InvokableA::class,
            InvokableB::class,
            InvokableC::class,
        ],
        'factories' => [
            FactoryA::class,
            FactoryB::class,
            FactoryC::class,
        ],
    ];
}

?>
