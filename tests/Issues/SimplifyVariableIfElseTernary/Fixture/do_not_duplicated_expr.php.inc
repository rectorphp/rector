<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\SimplifyVariableIfElseTernary\Fixture;

final class DoNotDuplicateExpr
{
    function test($foo, $bar)
    {
        if ($foo > 0) {
            $baz = 'a';
        } else {
            $baz = 'b';
        }

        return $baz;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\SimplifyVariableIfElseTernary\Fixture;

final class DoNotDuplicateExpr
{
    function test($foo, $bar)
    {
        return $foo > 0 ? 'a' : 'b';
    }
}

?>
