<?php

namespace RectorPrefix20210818;

if (\class_exists('t3lib_pageSelect_getPageOverlayHook')) {
    return;
}
class t3lib_pageSelect_getPageOverlayHook
{
}
\class_alias('t3lib_pageSelect_getPageOverlayHook', 't3lib_pageSelect_getPageOverlayHook', \false);
