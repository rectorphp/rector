<?php

namespace RectorPrefix20210916;

if (\class_exists('t3lib_cache_exception_DuplicateIdentifier')) {
    return;
}
class t3lib_cache_exception_DuplicateIdentifier
{
}
\class_alias('t3lib_cache_exception_DuplicateIdentifier', 't3lib_cache_exception_DuplicateIdentifier', \false);
