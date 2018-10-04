<?php declare(strict_types=1);

namespace OldNamespace\SubNamespace;

class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function create(\PHPUnit\Something $argument): \PHPUnit\Framework\Something
    {
        return new \PHPUnit\Framework\Something;
    }
}
