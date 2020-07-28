<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Source\OneToManyInterface;

class MultiReturn implements OneToManyInterface
{
    public function getNode(): string
    {
        if (true) {
            return 'Echo_';
        }

        return 'Exit_';
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\Source\OneToManyInterface;

class MultiReturn implements OneToManyInterface
{
    /**
     * @return string[]
     */
    public function getNodes(): array
    {
        if (true) {
            return ['Echo_'];
        }

        return ['Exit_'];
    }
}

?>
