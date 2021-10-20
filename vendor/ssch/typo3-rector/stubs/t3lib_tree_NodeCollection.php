<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_tree_NodeCollection')) {
    return;
}
class t3lib_tree_NodeCollection
{
}
\class_alias('t3lib_tree_NodeCollection', 't3lib_tree_NodeCollection', \false);
