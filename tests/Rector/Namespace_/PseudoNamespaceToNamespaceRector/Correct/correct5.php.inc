<?php declare(strict_types=1);

namespace ChangeMe\ToNamespaced;

use PHPUnit\TestCase;

class SomeTestCase
{
    /**
     * @return \ChangeMe_AnotherNamespace
     */
    public function someMethod(): \Keep_ThisThough
    {
        if ($this instanceof \PHPUnit\TestCase) {
            return true;
        }
    }
}
