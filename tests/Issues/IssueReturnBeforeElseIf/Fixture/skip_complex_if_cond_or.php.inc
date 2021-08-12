<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueReturnBeforeElseIf\Fixture;

class SkipComplexIfCondOr
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
