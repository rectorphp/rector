<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueReturnBeforeElseIf\Fixture;

class ComplexIfCondOr
{
    public function run($a, $b, $c, $d, $e)
    {
        if (($a && false === $b) || ! $c) {
            return '';
        } elseif ($d) {
            return '';
        }
        return $e;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueReturnBeforeElseIf\Fixture;

class ComplexIfCondOr
{
    public function run($a, $b, $c, $d, $e)
    {
        if (($a && false === $b) || ! $c) {
            return '';
        }
        if ($d) {
            return '';
        }
        return $e;
    }
}

?>