<?php declare(strict_types=1);

namespace OldNamespace\SubNamespace;

class SomeTest extends \PHPUnit_Framework_TestCase
{
    public function create(\PHPUnit_Something $argument): \PHPUnit_Framework_Something
    {
        return new \PHPUnit_Framework_Something;
    }
}
