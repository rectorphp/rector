<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_tree_ComparableNode')) {
    return;
}
class t3lib_tree_ComparableNode
{
}
\class_alias('t3lib_tree_ComparableNode', 't3lib_tree_ComparableNode', \false);
