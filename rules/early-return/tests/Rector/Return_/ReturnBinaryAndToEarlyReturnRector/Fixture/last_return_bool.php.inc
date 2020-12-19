<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class LastReturnBool
{
    public function accept($something)
    {
        return $something && $this->getSomethingElse();
    }

    public function accept2($something, $somethingelse)
    {
        return $something && $somethingelse === 'something else';
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

    public function accept2($something, $somethingelse)
    {
        if (!$something) {
            return false;
        }
        return $somethingelse === 'something else';
    }

    private function getSomethingElse(): bool
    {
        return true;
    }
}

?>
