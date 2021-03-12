<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\BIGLetterFirst;

class LetterUpper
{
    public function __construct(BIGLetterFirst $random)
    {
        $this->random = $random;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\BIGLetterFirst;

class LetterUpper
{
    public function __construct(BIGLetterFirst $bigLetterFirst)
    {
        $this->random = $bigLetterFirst;
    }
}

?>
