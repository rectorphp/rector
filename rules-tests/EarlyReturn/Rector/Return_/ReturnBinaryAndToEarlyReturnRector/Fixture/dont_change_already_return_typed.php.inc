<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class DontChangeAlreadyReturnTyped
{
    public function accept()
    {
        return $this->something() && $this->somethingElse();
    }

    private function something(): bool
    {
        return true;
    }

    private function somethingElse(): bool
    {
        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class DontChangeAlreadyReturnTyped
{
    public function accept()
    {
        if (!$this->something()) {
            return false;
        }
        return $this->somethingElse();
    }

    private function something(): bool
    {
        return true;
    }

    private function somethingElse(): bool
    {
        return true;
    }
}

?>
