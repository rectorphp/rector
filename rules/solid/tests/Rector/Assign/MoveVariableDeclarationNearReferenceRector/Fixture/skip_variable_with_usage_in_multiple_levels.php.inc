<?php

function skipVariableWithUsageInMultipleLevels()
{
    $varused = 1;

    if (mktime() === false) {
        return $varused;
    }

    echo $varused;
}
