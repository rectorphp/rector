<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class DonotChangeVariableNextTryReassignDifferent
{
    public function run()
    {
        try {
        } catch (SomeException $typoException) {
        }

        $this->verify($typoException);

        $typoException = 'test';
        $this->verify($typoException);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class DonotChangeVariableNextTryReassignDifferent
{
    public function run()
    {
        try {
        } catch (SomeException $someException) {
        }

        $this->verify($someException);

        $typoException = 'test';
        $this->verify($typoException);
    }
}

?>
