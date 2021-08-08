<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueEarlyReturnAndOr\Fixture;

class ComplexAndOr
{
    public function run($a, $b, $c, $d)
    {
        if (($a && false === $b) || ! $c) {
            return '';
        }

        return $d;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueEarlyReturnAndOr\Fixture;

class ComplexAndOr
{
    public function run($a, $b, $c, $d)
    {
        if ($a && false === $b) {
            return '';
        }
        if (! $c) {
            return '';
        }
        return $d;
    }
}

?>
