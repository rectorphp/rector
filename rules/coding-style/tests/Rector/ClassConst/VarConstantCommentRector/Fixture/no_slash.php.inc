<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector;

class SomeClass
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

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector;

class SomeClass
{
    /**
     * Some text comment
     * @var string[][]
     */
    private const CONTROLS_MAPPING = [['a']];
}

?>
