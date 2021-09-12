<?php

namespace Rector\Core\Tests\Issues\IssueVarConstantNewline\Fixture;

class Fixture
{
    public const A = 'a';

    public const B = 'b';
    /**
     * @var string
     */
    public const C = 'c';
    public const D = 'd';
    public const E = 'e';
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\IssueVarConstantNewline\Fixture;

class Fixture
{
    /**
     * @var string
     */
    public const A = 'a';

    /**
     * @var string
     */
    public const B = 'b';

    /**
     * @var string
     */
    public const C = 'c';

    /**
     * @var string
     */
    public const D = 'd';

    /**
     * @var string
     */
    public const E = 'e';
}

?>
