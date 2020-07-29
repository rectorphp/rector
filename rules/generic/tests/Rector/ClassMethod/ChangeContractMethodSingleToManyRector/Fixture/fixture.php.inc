<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Source\OneToManyInterface;

class SomeClass implements OneToManyInterface
{
    public function getNode(): string
    {
        return 'Echo_';
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Source\OneToManyInterface;

class SomeClass implements OneToManyInterface
{
    /**
     * @return string[]
     */
    public function getNodes(): array
    {
        return ['Echo_'];
    }
}

?>
