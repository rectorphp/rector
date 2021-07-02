<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class ChangeVariableNextTry
{
    public function run()
    {
        try {
        } catch (SomeException $typoException) {
        }

        $this->verify($typoException);
        $this->verify2($typoException);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class ChangeVariableNextTry
{
    public function run()
    {
        try {
        } catch (SomeException $someException) {
        }

        $this->verify($someException);
        $this->verify2($someException);
    }
}

?>
