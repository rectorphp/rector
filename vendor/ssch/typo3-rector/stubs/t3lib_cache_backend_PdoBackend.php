<?php

namespace RectorPrefix20210818;

if (\class_exists('t3lib_cache_backend_PdoBackend')) {
    return;
}
class t3lib_cache_backend_PdoBackend
{
}
\class_alias('t3lib_cache_backend_PdoBackend', 't3lib_cache_backend_PdoBackend', \false);
