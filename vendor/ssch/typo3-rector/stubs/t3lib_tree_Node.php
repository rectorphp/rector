<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_tree_Node')) {
    return;
}
class t3lib_tree_Node
{
}
\class_alias('t3lib_tree_Node', 't3lib_tree_Node', \false);
