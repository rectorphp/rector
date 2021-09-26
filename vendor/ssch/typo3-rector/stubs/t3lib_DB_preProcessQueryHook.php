<?php

namespace RectorPrefix20210926;

if (\class_exists('t3lib_DB_preProcessQueryHook')) {
    return;
}
class t3lib_DB_preProcessQueryHook
{
}
\class_alias('t3lib_DB_preProcessQueryHook', 't3lib_DB_preProcessQueryHook', \false);
