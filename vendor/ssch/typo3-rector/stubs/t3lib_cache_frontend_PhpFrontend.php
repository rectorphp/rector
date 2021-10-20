<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_cache_frontend_PhpFrontend')) {
    return;
}
class t3lib_cache_frontend_PhpFrontend
{
}
\class_alias('t3lib_cache_frontend_PhpFrontend', 't3lib_cache_frontend_PhpFrontend', \false);
