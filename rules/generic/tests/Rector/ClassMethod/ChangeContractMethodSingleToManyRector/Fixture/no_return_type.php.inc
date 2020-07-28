<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Source\OneToManyInterface;

class NoReturnType implements OneToManyInterface
{
    public function getNode()
    {
        return 'Echo_';
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Source\OneToManyInterface;

class NoReturnType implements OneToManyInterface
{
    public function getNodes(): array
    {
        return ['Echo_'];
    }
}

?>
