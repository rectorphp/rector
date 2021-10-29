<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

use MyCLabs\Enum\Enum;

final class SkipInstanceOfEnum
{
    public function run(object $object)
    {
        if ($object instanceof Enum) {
            return;
        }

        return $this->processTheRest($object);
    }

    private function processTheRest($object)
    {
        return $object;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

use MyCLabs\Enum\Enum;

final class SkipInstanceOfEnum
{
    public function run(object $object)
    {
        if ($object instanceof Enum) {
            return;
        }

        return $this->processTheRest($object);
    }

    private function processTheRest(object $object)
    {
        return $object;
    }
}

?>
