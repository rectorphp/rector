<?php

function skipVariableWithUsageInMultipleLevels()
{
    $varused = 1;

    if (mktime() === false) {
        if (true) {
            if (false) {
                return $varused;
            }
        }
    }

    echo $varused;
}
