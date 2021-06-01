<?php

namespace RectorPrefix20210601;

if (\class_exists('t3lib_pageSelect_getRecordOverlayHook')) {
    return;
}
class t3lib_pageSelect_getRecordOverlayHook
{
}
\class_alias('t3lib_pageSelect_getRecordOverlayHook', 't3lib_pageSelect_getRecordOverlayHook', \false);
