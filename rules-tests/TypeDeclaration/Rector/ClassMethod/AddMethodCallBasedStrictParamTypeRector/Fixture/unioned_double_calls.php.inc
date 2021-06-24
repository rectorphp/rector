<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class UnionedDoubleCalls
{
    public function run()
    {
        $this->getById(5);
        $this->getById(null);
    }

    private function getById($id)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class UnionedDoubleCalls
{
    public function run()
    {
        $this->getById(5);
        $this->getById(null);
    }

    private function getById(?int $id)
    {
    }
}

?>
