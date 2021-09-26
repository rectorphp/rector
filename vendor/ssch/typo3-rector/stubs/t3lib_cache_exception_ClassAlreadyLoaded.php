<?php

namespace RectorPrefix20210926;

if (\class_exists('t3lib_cache_exception_ClassAlreadyLoaded')) {
    return;
}
class t3lib_cache_exception_ClassAlreadyLoaded
{
}
\class_alias('t3lib_cache_exception_ClassAlreadyLoaded', 't3lib_cache_exception_ClassAlreadyLoaded', \false);
