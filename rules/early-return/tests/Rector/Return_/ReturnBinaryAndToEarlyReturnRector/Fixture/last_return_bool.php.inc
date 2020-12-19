<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class LastReturnBool
{
    public function accept($something)
    {
        return $something && $this->getSomethingElse();
    }

    private function getSomethingElse(): bool
    {
        return true;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class LastReturnBool
{
    public function accept($something)
    {
        if (!$something) {
            return false;
        }
        return $this->getSomethingElse();
    }

    private function getSomethingElse(): bool
    {
        return true;
    }
}

?>
