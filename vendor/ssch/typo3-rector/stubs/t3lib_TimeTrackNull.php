<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_TimeTrackNull')) {
    return;
}
class t3lib_TimeTrackNull
{
}
\class_alias('t3lib_TimeTrackNull', 't3lib_TimeTrackNull', \false);
