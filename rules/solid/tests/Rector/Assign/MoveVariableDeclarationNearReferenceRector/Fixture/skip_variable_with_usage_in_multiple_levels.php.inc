<?php

function skipVariableWithUsageInMultipleLevels()
{
    $varused = 1;

    if (mktime() === false) {
        if (true) {
            return $varused;
        }
    }

    echo $varused;
}
