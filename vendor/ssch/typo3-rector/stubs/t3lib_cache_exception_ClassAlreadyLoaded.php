<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_cache_exception_ClassAlreadyLoaded')) {
    return;
}
class t3lib_cache_exception_ClassAlreadyLoaded
{
}
\class_alias('t3lib_cache_exception_ClassAlreadyLoaded', 't3lib_cache_exception_ClassAlreadyLoaded', \false);
