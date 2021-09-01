<?php

namespace Rector\Core\Tests\Issues\Issue6670\Fixture;

final class DoNotRemoveUsedClassMethod
{
    public function doSomething(): int
    {
        if (true === false) {
            return -1;
        } else {
            return $this->notUnused();
        }
    }

    private function notUnused(): int
    {
        // This is some code that is very important
    }
}
?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6670\Fixture;

final class DoNotRemoveUsedClassMethod
{
    public function doSomething(): int
    {
        if (true === false) {
            return -1;
        }
        return $this->notUnused();
    }

    private function notUnused(): int
    {
        // This is some code that is very important
    }
}
?>
