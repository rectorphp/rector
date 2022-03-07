<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class ChildHasPriority extends ParentClassWithDefinedReturn
{
    public function getData()
    {
        return [
            [
                'a' => 'string',
                'b' => 1,
                'c' => 1.0
            ]
        ];
    }
}


abstract class ParentClassWithDefinedReturn
{
    /**
     * @return mixed[]
     */
    public function getData()
    {
        return ['...'];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class ChildHasPriority extends ParentClassWithDefinedReturn
{
    /**
     * @return array<int, array{a: string, b: int, c: float}>
     */
    public function getData()
    {
        return [
            [
                'a' => 'string',
                'b' => 1,
                'c' => 1.0
            ]
        ];
    }
}


abstract class ParentClassWithDefinedReturn
{
    /**
     * @return mixed[]
     */
    public function getData()
    {
        return ['...'];
    }
}

?>
