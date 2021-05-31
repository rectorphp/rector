<?php

namespace RectorPrefix20210531;

if (\class_exists('t3lib_DB')) {
    return;
}
class t3lib_DB
{
}
\class_alias('t3lib_DB', 't3lib_DB', \false);
