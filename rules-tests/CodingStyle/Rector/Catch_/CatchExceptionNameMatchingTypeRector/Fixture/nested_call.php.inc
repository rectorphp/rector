<?php

namespace Rector\CodingStyle\Tests\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class NestedCall
{
    public function run()
    {
        try {
            // ...
        } catch (SomeException $typoException) {
            $typoException->getMessage();

            if ($typoException) {
                $processMe = function () use ($typoException) {
                    return (bool) $typoException;
                };
            }
        }
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class NestedCall
{
    public function run()
    {
        try {
            // ...
        } catch (SomeException $someException) {
            $someException->getMessage();

            if ($someException) {
                $processMe = function () use ($someException) {
                    return (bool) $someException;
                };
            }
        }
    }
}

?>
