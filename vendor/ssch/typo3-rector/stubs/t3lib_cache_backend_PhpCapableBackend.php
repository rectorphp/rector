<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_cache_backend_PhpCapableBackend')) {
    return;
}
class t3lib_cache_backend_PhpCapableBackend
{
}
\class_alias('t3lib_cache_backend_PhpCapableBackend', 't3lib_cache_backend_PhpCapableBackend', \false);
