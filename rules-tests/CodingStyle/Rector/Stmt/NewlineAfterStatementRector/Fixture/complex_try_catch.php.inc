<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

class ComplexTryCatch
{
    public function run()
    {
        try {
            if (rand(0, 1)) {

            }
        } catch (\RuntimeException $e) {
            if (rand(0, 1)) {

            }
        } finally {

        }
        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

class ComplexTryCatch
{
    public function run()
    {
        try {
            if (rand(0, 1)) {

            }
        } catch (\RuntimeException $e) {
            if (rand(0, 1)) {

            }
        } finally {

        }

        return true;
    }
}

?>