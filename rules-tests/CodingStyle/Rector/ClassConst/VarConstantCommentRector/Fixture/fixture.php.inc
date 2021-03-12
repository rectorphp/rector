<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

class Fixture
{
    const HI = 'hi';
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

class Fixture
{
    /**
     * @var string
     */
    const HI = 'hi';
}

?>
