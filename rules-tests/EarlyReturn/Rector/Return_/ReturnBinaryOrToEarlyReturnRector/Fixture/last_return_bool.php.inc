<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class LastReturnBool
{
    public function accept()
    {
        return $this->something() || $this->getSomethingElse();
    }

    public function accept2()
    {
        return $this->something() || $this->somethingelse() === 'something else';
    }

    private function getSomethingElse(): bool
    {
        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class LastReturnBool
{
    public function accept()
    {
        if ($this->something()) {
            return true;
        }
        return $this->getSomethingElse();
    }

    public function accept2()
    {
        if ($this->something()) {
            return true;
        }
        return $this->somethingelse() === 'something else';
    }

    private function getSomethingElse(): bool
    {
        return true;
    }
}

?>
