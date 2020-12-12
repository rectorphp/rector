<?php

namespace Rector\CodingStyle\Tests\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class PdoClass
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

namespace Rector\CodingStyle\Tests\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class PdoClass
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
