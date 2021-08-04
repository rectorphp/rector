<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueDowngradeMixedType\Fixture;

use Rector\Core\Tests\Issues\IssueDowngradeMixedType\Source\SomeClass;

class AnotherClass
{
    private SomeClass $foo;

    public function bar(SomeClass $baz)
    {
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueDowngradeMixedType\Fixture;

use Rector\Core\Tests\Issues\IssueDowngradeMixedType\Source\SomeClass;

class AnotherClass
{
    /**
     * @var SomeClass
     */
    private $foo;

    public function bar(SomeClass $baz)
    {
    }
}

?>
