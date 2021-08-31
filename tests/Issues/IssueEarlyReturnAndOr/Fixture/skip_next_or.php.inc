<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueEarlyReturnAndOr\Fixture;

class SkipAndNextOr
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
