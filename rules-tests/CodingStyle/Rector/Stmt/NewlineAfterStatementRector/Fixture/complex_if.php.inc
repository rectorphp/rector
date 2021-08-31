<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

class ComplexIf
{
    public function run()
    {
        if (rand(0, 1)) {
            if (rand(0, 1)) {

            }
        } elseif (rand(0, 1)) {

        } else {

        }
        return true;
    }

    public function run2()
    {
        if (rand(0, 1)) {
            if (rand(0, 1)) {

            }
            echo 'test';
        } elseif (rand(0, 1)) {

        } else {

        }
        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

class ComplexIf
{
    public function run()
    {
        if (rand(0, 1)) {
            if (rand(0, 1)) {

            }
        } elseif (rand(0, 1)) {

        } else {

        }

        return true;
    }

    public function run2()
    {
        if (rand(0, 1)) {
            if (rand(0, 1)) {

            }

            echo 'test';
        } elseif (rand(0, 1)) {

        } else {

        }

        return true;
    }
}

?>