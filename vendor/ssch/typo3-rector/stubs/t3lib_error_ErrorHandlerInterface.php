<?php

namespace RectorPrefix20211023;

if (\interface_exists('t3lib_error_ErrorHandlerInterface')) {
    return;
}
interface t3lib_error_ErrorHandlerInterface
{
}
\class_alias('t3lib_error_ErrorHandlerInterface', 't3lib_error_ErrorHandlerInterface', \false);
