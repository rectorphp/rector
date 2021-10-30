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
     * @var array<string, array<class-string<\Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture\FactoryA>|class-string<\Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture\FactoryB>|class-string<\Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture\FactoryC>|class-string<\Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture\InvokableA>|class-string<\Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture\InvokableB>|class-string<\Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture\InvokableC>>>
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
