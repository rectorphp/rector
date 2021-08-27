<?php

namespace RectorPrefix20210827;

if (\class_exists('t3lib_matchCondition_backend')) {
    return;
}
class t3lib_matchCondition_backend
{
}
\class_alias('t3lib_matchCondition_backend', 't3lib_matchCondition_backend', \false);
