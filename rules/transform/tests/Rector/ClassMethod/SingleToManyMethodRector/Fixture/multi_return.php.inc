<?php

namespace Rector\Transform\Tests\Rector\ClassMethod\SingleToManyMethodRector\Fixture;

use Rector\Transform\Tests\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;

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

namespace Rector\Transform\Tests\Rector\ClassMethod\SingleToManyMethodRector\Fixture;

use Rector\Transform\Tests\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;

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
