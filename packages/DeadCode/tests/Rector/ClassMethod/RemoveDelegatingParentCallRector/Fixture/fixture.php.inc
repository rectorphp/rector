<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

class SomeClass
{
    public function prettyPrint(array $stmts): string
    {
        return parent::prettyPrint($stmts);
    }

    public function process(array $stmts): void
    {
        parent::process($stmts);
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

class SomeClass
{
}

?>
