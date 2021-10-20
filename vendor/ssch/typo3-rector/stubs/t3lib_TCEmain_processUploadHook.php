<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_TCEmain_processUploadHook')) {
    return;
}
class t3lib_TCEmain_processUploadHook
{
}
\class_alias('t3lib_TCEmain_processUploadHook', 't3lib_TCEmain_processUploadHook', \false);
