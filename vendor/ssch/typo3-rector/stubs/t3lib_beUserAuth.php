<?php

namespace RectorPrefix20210827;

if (\class_exists('t3lib_beUserAuth')) {
    return;
}
class t3lib_beUserAuth
{
}
\class_alias('t3lib_beUserAuth', 't3lib_beUserAuth', \false);
