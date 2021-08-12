<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueReturnBeforeElseIf\Fixture;

class ComplexIfCondOrWithoutElseIf
{
    public function run($a, $b, $c)
    {
        if (($a && false === $b) || ! $c) {
            return 'a';
        } else {
            return 'b';
        }
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueReturnBeforeElseIf\Fixture;

class ComplexIfCondOrWithoutElseIf
{
    public function run($a, $b, $c)
    {
        if ($a && false === $b) {
            return 'a';
        }
        if (! $c) {
            return 'a';
        }
        return 'b';
    }
}

?>
