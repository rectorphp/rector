<?php declare(strict_types=1);

namespace OldNamespace\SubNamespace;

use PHPUnit_SpecialClass;

class SomeTest extends \PHPUnit_Framework_TestCase
{
    public function create(\PHPUnit_Something $argument): \PHPUnit_Something
    {
        $specialClass = new PHPUnit_SpecialClass;

        return new \PHPUnit_Framework_Something;
    }
}
