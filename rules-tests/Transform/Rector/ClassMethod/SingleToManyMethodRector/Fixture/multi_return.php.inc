<?php

namespace Rector\Tests\Transform\Rector\ClassMethod\SingleToManyMethodRector\Fixture;

use Rector\Tests\Transform\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;

class MultiReturn implements OneToManyInterface
{
    /**
     * @return class-string<\PhpParser\Node>
     */
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

namespace Rector\Tests\Transform\Rector\ClassMethod\SingleToManyMethodRector\Fixture;

use Rector\Tests\Transform\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;

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
