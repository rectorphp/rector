<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\BIGLetterFirst;

class SkipLetterUpper
{
    public function __construct(BIGLetterFirst $random)
    {
        $this->random = $random;
    }
}

?>
