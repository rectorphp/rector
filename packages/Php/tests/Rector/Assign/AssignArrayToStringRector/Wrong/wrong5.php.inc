<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Assign\AssignArrayToStringRector\Wrong;

class WithSomeInMethodPropertySetDefaultAbove
{
    private $property;

    public function run()
    {
        $this->property = '';
        $this->property[] = 1;
    }
}
