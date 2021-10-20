<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_codec_JavaScriptEncoder')) {
    return;
}
class t3lib_codec_JavaScriptEncoder
{
}
\class_alias('t3lib_codec_JavaScriptEncoder', 't3lib_codec_JavaScriptEncoder', \false);
