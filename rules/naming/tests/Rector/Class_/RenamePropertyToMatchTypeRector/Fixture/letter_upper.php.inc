<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\BIGLetterFirst;

class LetterUpper
{
    public function __construct(BIGLetterFirst $random)
    {
        $this->random = $random;
    }
}

?>
