<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class SomeClass
{
    const HI = 'hi';
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class SomeClass
{
    /**
     * @var string
     */
    const HI = 'hi';
}

?>
