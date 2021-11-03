<?php

namespace RectorPrefix20211103;

if (\class_exists('tslib_content_fileLinkHook')) {
    return;
}
class tslib_content_fileLinkHook
{
}
\class_alias('tslib_content_fileLinkHook', 'tslib_content_fileLinkHook', \false);
