<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class Comment
{
    public function accept()
    {
        // a comment
        if (rand(0, 1)) {
            // another comment
            return $this->something() && $this->somethingElse();
        }

        // another next comment
        return 1;
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class Comment
{
    public function accept()
    {
        // a comment
        if (rand(0, 1)) {
            // another comment
            if (!$this->something()) {
                return false;
            }
            return (bool) $this->somethingElse();
        }

        // another next comment
        return 1;
    }
}

?>
