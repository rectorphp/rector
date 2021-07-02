<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class ChangeVariableNextTry2
{
    public function run()
    {
        if (rand(0, 1)) {
            try {
            } catch (SomeException $typoException) {
            }
        }

        if (isset($typoException)) {
            $this->verify($typoException);
            $this->verify2($typoException);
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class ChangeVariableNextTry2
{
    public function run()
    {
        if (rand(0, 1)) {
            try {
            } catch (SomeException $someException) {
            }
        }

        if (isset($someException)) {
            $this->verify($someException);
            $this->verify2($someException);
        }
    }
}

?>
