<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_TimeTrackNull')) {
    return;
}
class t3lib_TimeTrackNull
{
}
\class_alias('t3lib_TimeTrackNull', 't3lib_TimeTrackNull', \false);
