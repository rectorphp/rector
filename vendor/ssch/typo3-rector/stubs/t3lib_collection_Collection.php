<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_collection_Collection')) {
    return;
}
class t3lib_collection_Collection
{
}
\class_alias('t3lib_collection_Collection', 't3lib_collection_Collection', \false);
