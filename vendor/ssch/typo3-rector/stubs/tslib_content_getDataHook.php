<?php

namespace RectorPrefix20211020;

if (\class_exists('tslib_content_getDataHook')) {
    return;
}
class tslib_content_getDataHook
{
}
\class_alias('tslib_content_getDataHook', 'tslib_content_getDataHook', \false);
