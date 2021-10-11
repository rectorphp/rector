<?php

namespace RectorPrefix20211011;

if (\class_exists('t3lib_tree_AbstractDataProvider')) {
    return;
}
class t3lib_tree_AbstractDataProvider
{
}
\class_alias('t3lib_tree_AbstractDataProvider', 't3lib_tree_AbstractDataProvider', \false);
