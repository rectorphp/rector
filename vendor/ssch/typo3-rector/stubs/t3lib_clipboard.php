<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_clipboard')) {
    return;
}
class t3lib_clipboard
{
}
\class_alias('t3lib_clipboard', 't3lib_clipboard', \false);
