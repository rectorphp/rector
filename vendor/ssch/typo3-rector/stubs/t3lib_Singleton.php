<?php

namespace RectorPrefix20210729;

if (\class_exists('t3lib_Singleton')) {
    return;
}
class t3lib_Singleton
{
}
\class_alias('t3lib_Singleton', 't3lib_Singleton', \false);
