<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class ChangeVariableNextTryReassignSameVariable
{
    public function run()
    {
        try {
        } catch (SomeException $typoException) {
        }

        $typoException = $typoException;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class ChangeVariableNextTryReassignSameVariable
{
    public function run()
    {
        try {
        } catch (SomeException $someException) {
        }

        $someException = $someException;
    }
}

?>
