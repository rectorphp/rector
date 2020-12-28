<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class Fixture
{
    const HI = 'hi';
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

class Fixture
{
    /**
     * @var string
     */
    const HI = 'hi';
}

?>
