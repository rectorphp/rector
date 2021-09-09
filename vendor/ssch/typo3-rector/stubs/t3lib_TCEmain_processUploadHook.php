<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_TCEmain_processUploadHook')) {
    return;
}
class t3lib_TCEmain_processUploadHook
{
}
\class_alias('t3lib_TCEmain_processUploadHook', 't3lib_TCEmain_processUploadHook', \false);
