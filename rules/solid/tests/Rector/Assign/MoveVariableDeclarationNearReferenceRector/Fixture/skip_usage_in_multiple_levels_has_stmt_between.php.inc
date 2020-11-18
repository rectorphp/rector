<?php

function skipVariableWithUsageInMultipleLevelsHasStmtBetween()
{
    $varused = 1;

    if (mktime() === false) {
        if (true) {
            if (false) {
                return $varused;
            }
        }
    }

    echo 'STATEMENT HERE';

    echo $varused;
}
