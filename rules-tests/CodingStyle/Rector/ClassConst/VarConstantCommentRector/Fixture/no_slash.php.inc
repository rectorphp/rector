<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

class NoSlash
{
    /**
     * Some text comment
     */
    private const CONTROLS_MAPPING = [['a']];
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

class NoSlash
{
    /**
     * Some text comment
     * @var string[][]
     */
    private const CONTROLS_MAPPING = [['a']];
}

?>
