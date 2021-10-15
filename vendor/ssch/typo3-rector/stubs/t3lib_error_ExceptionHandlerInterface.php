<?php

namespace RectorPrefix20211015;

if (\interface_exists('t3lib_error_ExceptionHandlerInterface')) {
    return;
}
interface t3lib_error_ExceptionHandlerInterface
{
}
\class_alias('t3lib_error_ExceptionHandlerInterface', 't3lib_error_ExceptionHandlerInterface', \false);
