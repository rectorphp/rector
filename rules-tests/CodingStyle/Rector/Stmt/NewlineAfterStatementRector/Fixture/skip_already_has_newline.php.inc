<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

class SkipAlreadyHasNewline
{
    private $property;

    private $property2;

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