<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

class NoNewLine
{
    private $property;
    private $property2;
    private const VIEW = 'view';
    private const ADD = 'add';
    public function run(array $data)
    {
        if (rand(0, 1)) {

        }
        echo 'test';

        foreach ($data as $key => $value) {
            # code...
        }
        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

class NoNewLine
{
    private $property;

    private $property2;

    private const VIEW = 'view';

    private const ADD = 'add';

    public function run(array $data)
    {
        if (rand(0, 1)) {

        }

        echo 'test';

        foreach ($data as $key => $value) {
            # code...
        }

        return true;
    }
}

?>
