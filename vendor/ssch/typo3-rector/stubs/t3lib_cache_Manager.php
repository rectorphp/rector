<?php

namespace RectorPrefix20210827;

if (\class_exists('t3lib_cache_Manager')) {
    return;
}
class t3lib_cache_Manager
{
}
\class_alias('t3lib_cache_Manager', 't3lib_cache_Manager', \false);
