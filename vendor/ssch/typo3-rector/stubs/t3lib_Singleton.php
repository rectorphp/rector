<?php

namespace RectorPrefix20211102;

if (\class_exists('t3lib_Singleton')) {
    return;
}
class t3lib_Singleton
{
}
\class_alias('t3lib_Singleton', 't3lib_Singleton', \false);
