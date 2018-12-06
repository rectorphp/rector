<?php

function simplifyIfReturnBool9()
{
    if ($dateTimePattern->getTimeType() !== null && $dateTimePattern->getTimeType() !== $timeType) {
        return false;
    }

    return true;
}

?>
-----
<?php

function simplifyIfReturnBool9()
{
    if ($dateTimePattern->getTimeType() !== null && $dateTimePattern->getTimeType() !== $timeType) {
        return false;
    }

    return true;
}

?>
