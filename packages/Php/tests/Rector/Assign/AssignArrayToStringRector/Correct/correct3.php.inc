<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Wrong;

class WithSomeStaticString
{
    public $someString = [];

    public $anotherString = '';

    public function run()
    {
        $this->someString[] = 1;

        $this->anotherString[1] = 1;
    }
}
