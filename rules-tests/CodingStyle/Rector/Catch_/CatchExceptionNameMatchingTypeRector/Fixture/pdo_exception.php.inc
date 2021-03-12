<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class PdoException
{
    public function run()
    {
        try {
            // ...
        } catch (PDOException $e) {
            $e->getMessage();
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class PdoException
{
    public function run()
    {
        try {
            // ...
        } catch (PDOException $pdoException) {
            $pdoException->getMessage();
        }
    }
}

?>
