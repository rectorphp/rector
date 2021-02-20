<?php

namespace Rector\Arguments\Tests\Rector\ClassMethod\SingleToManyMethodRector\Fixture;

use Rector\Arguments\Tests\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;

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

namespace Rector\Arguments\Tests\Rector\ClassMethod\SingleToManyMethodRector\Fixture;

use Rector\Arguments\Tests\Rector\ClassMethod\SingleToManyMethodRector\Source\OneToManyInterface;

class NoReturnType implements OneToManyInterface
{
    public function getNodes(): array
    {
        return ['Echo_'];
    }
}

?>
