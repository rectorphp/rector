<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

final class SkipAssignEmbedHtml
{
    public function content()
    {
        for ($c = 0; $c <= \pg_numrows($result); $c++) {
            if ($a !== $b) {
                if ($row === 0) {
                    StaticCaller::callMe($suspect);
                }
                $suspect = 0;
            }
            if ($row[5] === 1) {
                $suspect = $row[6];
            }
        }
    }
}
