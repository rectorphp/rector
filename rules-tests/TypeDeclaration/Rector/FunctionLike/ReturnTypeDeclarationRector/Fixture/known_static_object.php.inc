<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class Tiny
{

}

class KnownStaticObject
{
    public function getObject()
    {
        $object = new \stdClass;
        return $object;
    }

    public function getTiny()
    {
        $object = new Tiny();
        return $object;
    }

    public function getMaybe($value)
    {
        $object = null;
        if ($value) {
            $object = new \stdClass;
        }
        return $object;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class Tiny
{

}

class KnownStaticObject
{
    public function getObject(): \stdClass
    {
        $object = new \stdClass;
        return $object;
    }

    public function getTiny(): \Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\Tiny
    {
        $object = new Tiny();
        return $object;
    }

    public function getMaybe($value): ?\stdClass
    {
        $object = null;
        if ($value) {
            $object = new \stdClass;
        }
        return $object;
    }
}

?>
