<?php

if ($dateTimePattern->getTimeType() !== null && $dateTimePattern->getTimeType() !== $timeType) {
    return false;
}

return true;
