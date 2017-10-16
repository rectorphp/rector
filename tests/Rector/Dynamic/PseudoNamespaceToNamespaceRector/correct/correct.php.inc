<?php declare(strict_types=1);

namespace OldNamespace\SubNamespace;

use PHPUnit\SpecialClass;

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function create(\PHPUnit\Something $argument): \PHPUnit\Something
    {
        $specialClass = new SpecialClass;

        return new \PHPUnit\Framework\Something;
    }
}
