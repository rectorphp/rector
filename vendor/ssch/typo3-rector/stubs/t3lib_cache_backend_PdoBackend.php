<?php

namespace RectorPrefix20210902;

if (\class_exists('t3lib_cache_backend_PdoBackend')) {
    return;
}
class t3lib_cache_backend_PdoBackend
{
}
\class_alias('t3lib_cache_backend_PdoBackend', 't3lib_cache_backend_PdoBackend', \false);
