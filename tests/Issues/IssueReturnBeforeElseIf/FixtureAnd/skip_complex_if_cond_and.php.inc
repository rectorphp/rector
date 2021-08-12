<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueEarlyReturnAndInsideCase\Fixture;

class SkipComplexIfCondAnd
{
    public function run($a, $b, $c, $d, $e, $f, $g)
    {
        if ($b && $c) {
            return true;
        } elseif ($d && $e) {
            return false;
        } elseif ($f && $g) {
            return 1;
        }

        return 0;
    }
}

?>
