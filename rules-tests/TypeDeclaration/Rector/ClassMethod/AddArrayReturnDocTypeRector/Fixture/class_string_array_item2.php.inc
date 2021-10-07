<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class A {}
class B {}
class C {}
class CFactory {}
class D {}
class DFactory {}

class ClassStringArrayItem2
{
    public function getData()
    {
        return [
            A::class,
            B::class,
            C::class,
            CFactory::class,
            D::class,
            DFactory::class,
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

class ClassStringArrayItem2
{
    /**
     * @return class-string[]
     */
    public function getData()
    {
        return [
            A::class,
            B::class,
            C::class,
            CFactory::class,
            D::class,
            DFactory::class,
        ];
    }
}

?>
