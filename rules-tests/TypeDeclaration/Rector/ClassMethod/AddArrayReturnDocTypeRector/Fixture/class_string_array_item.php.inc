<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class A {}
class B {}
class C {}
class CFactory {}
class D {}
class DFactory {}

class ClassStringArrayItem
{
    public function getData()
    {
        return [
            'invokables' => [
                A::class => A::class,
                B::class => B::class,
            ],
            'factories'  => [
                C::class => CFactory::class,
                D::class => DFactory::class,
            ],
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class A {}
class B {}
class C {}
class CFactory {}
class D {}
class DFactory {}

class ClassStringArrayItem
{
    /**
     * @return array<string, mixed[]>
     */
    public function getData()
    {
        return [
            'invokables' => [
                A::class => A::class,
                B::class => B::class,
            ],
            'factories'  => [
                C::class => CFactory::class,
                D::class => DFactory::class,
            ],
        ];
    }
}

?>
