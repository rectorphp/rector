<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_cache_frontend_AbstractFrontend')) {
    return;
}
class t3lib_cache_frontend_AbstractFrontend
{
}
\class_alias('t3lib_cache_frontend_AbstractFrontend', 't3lib_cache_frontend_AbstractFrontend', \false);
