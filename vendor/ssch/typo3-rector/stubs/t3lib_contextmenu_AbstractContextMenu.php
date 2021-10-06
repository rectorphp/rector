<?php

namespace RectorPrefix20211006;

if (\class_exists('t3lib_contextmenu_AbstractContextMenu')) {
    return;
}
class t3lib_contextmenu_AbstractContextMenu
{
}
\class_alias('t3lib_contextmenu_AbstractContextMenu', 't3lib_contextmenu_AbstractContextMenu', \false);
