<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\FixtureWeakDocBlock;

final class UnionedDoubleCalls
{
    /**
     * @param int $value
     */
    public function run($value)
    {
        $this->getById($value);
    }

    private function getById($id)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\FixtureWeakDocBlock;

final class UnionedDoubleCalls
{
    /**
     * @param int $value
     */
    public function run($value)
    {
        $this->getById($value);
    }

    private function getById(int $id)
    {
    }
}

?>
