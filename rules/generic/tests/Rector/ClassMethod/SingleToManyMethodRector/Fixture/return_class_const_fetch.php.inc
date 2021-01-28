<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\SingleToManyMethodRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;

class ReturnClassConstFetch implements OneToManyInterface
{
    public function getNode(): string
    {
        return self::class;
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\SingleToManyMethodRector\Fixture;

use Rector\Generic\Tests\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;

class ReturnClassConstFetch implements OneToManyInterface
{
    /**
     * @return string[]
     */
    public function getNodes(): array
    {
        return [self::class];
    }
}

?>
