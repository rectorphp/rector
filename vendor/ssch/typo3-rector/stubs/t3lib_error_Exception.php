<?php

namespace RectorPrefix20210601;

if (\class_exists('t3lib_error_Exception')) {
    return;
}
class t3lib_error_Exception
{
}
\class_alias('t3lib_error_Exception', 't3lib_error_Exception', \false);
