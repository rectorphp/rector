<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_contextmenu_AbstractDataProvider')) {
    return;
}
class t3lib_contextmenu_AbstractDataProvider
{
}
\class_alias('t3lib_contextmenu_AbstractDataProvider', 't3lib_contextmenu_AbstractDataProvider', \false);
