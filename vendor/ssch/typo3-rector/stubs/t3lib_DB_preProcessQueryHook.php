<?php

namespace RectorPrefix20210531;

if (\class_exists('t3lib_DB_preProcessQueryHook')) {
    return;
}
class t3lib_DB_preProcessQueryHook
{
}
\class_alias('t3lib_DB_preProcessQueryHook', 't3lib_DB_preProcessQueryHook', \false);
