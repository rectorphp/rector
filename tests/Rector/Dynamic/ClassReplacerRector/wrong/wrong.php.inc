<?php declare(strict_types=1);

use Bagr;
use OldClass;
use NewClass;

class SomeClass
{
    public function create()
    {
        $newClass = new NewClass;

        return new OldClass;
    }
}
