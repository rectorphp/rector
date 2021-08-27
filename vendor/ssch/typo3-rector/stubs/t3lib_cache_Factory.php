<?php

namespace RectorPrefix20210827;

if (\class_exists('t3lib_cache_Factory')) {
    return;
}
class t3lib_cache_Factory
{
}
\class_alias('t3lib_cache_Factory', 't3lib_cache_Factory', \false);
