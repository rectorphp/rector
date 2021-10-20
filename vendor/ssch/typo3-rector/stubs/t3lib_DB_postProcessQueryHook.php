<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_DB_postProcessQueryHook')) {
    return;
}
class t3lib_DB_postProcessQueryHook
{
}
\class_alias('t3lib_DB_postProcessQueryHook', 't3lib_DB_postProcessQueryHook', \false);
