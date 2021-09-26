<?php

namespace RectorPrefix20210926;

if (\class_exists('t3lib_formprotection_Abstract')) {
    return;
}
class t3lib_formprotection_Abstract
{
}
\class_alias('t3lib_formprotection_Abstract', 't3lib_formprotection_Abstract', \false);
