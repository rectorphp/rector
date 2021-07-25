<?php

namespace RectorPrefix20210725;

if (\class_exists('t3lib_cache_backend_FileBackend')) {
    return;
}
class t3lib_cache_backend_FileBackend
{
}
\class_alias('t3lib_cache_backend_FileBackend', 't3lib_cache_backend_FileBackend', \false);
