<?php

namespace RectorPrefix20211020;

if (\class_exists('tslib_content_PostInitHook')) {
    return;
}
class tslib_content_PostInitHook
{
}
\class_alias('tslib_content_PostInitHook', 'tslib_content_PostInitHook', \false);
