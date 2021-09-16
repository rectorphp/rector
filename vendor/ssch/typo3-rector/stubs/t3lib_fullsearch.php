<?php

namespace RectorPrefix20210916;

if (\class_exists('t3lib_fullsearch')) {
    return;
}
class t3lib_fullsearch
{
}
\class_alias('t3lib_fullsearch', 't3lib_fullsearch', \false);
