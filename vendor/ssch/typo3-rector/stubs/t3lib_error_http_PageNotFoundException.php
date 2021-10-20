<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_error_http_PageNotFoundException')) {
    return;
}
class t3lib_error_http_PageNotFoundException
{
}
\class_alias('t3lib_error_http_PageNotFoundException', 't3lib_error_http_PageNotFoundException', \false);
