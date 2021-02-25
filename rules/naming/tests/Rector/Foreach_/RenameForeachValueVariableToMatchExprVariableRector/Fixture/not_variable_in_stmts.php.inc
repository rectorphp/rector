<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class NotVariableInStmts
{
    public function run()
    {
        foreach ($variables as $property) {
            $this->variable[] = $property;
        }
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class NotVariableInStmts
{
    public function run()
    {
        foreach ($variables as $variable) {
            $this->variable[] = $variable;
        }
    }
}

?>