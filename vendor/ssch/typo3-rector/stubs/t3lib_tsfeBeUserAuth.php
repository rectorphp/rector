<?php

namespace RectorPrefix20210714;

if (\class_exists('t3lib_tsfeBeUserAuth')) {
    return;
}
class t3lib_tsfeBeUserAuth
{
}
\class_alias('t3lib_tsfeBeUserAuth', 't3lib_tsfeBeUserAuth', \false);
