<?php

namespace Rector\Tests\CodingStyle\Rector\If_\NullableCompareToNullRector\Fixture;

use stdClass;

class SomeClass
{
    public function run()
    {
        if ($this->getNullOrObject()) {
            return true;
        }

        return false;
    }

    private function getNullOrObject(): ?stdClass
    {
        return null;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\If_\NullableCompareToNullRector\Fixture;

use stdClass;

class SomeClass
{
    public function run()
    {
        if ($this->getNullOrObject() !== null) {
            return true;
        }

        return false;
    }

    private function getNullOrObject(): ?stdClass
    {
        return null;
    }
}

?>
