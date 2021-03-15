<?php

namespace Rector\Tests\Transform\Rector\ClassMethod\SingleToManyMethodRector\Fixture;

use Rector\Tests\Transform\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;

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

namespace Rector\Tests\Transform\Rector\ClassMethod\SingleToManyMethodRector\Fixture;

use Rector\Tests\Transform\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;

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
