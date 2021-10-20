<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_userAuth')) {
    return;
}
class t3lib_userAuth
{
}
\class_alias('t3lib_userAuth', 't3lib_userAuth', \false);
