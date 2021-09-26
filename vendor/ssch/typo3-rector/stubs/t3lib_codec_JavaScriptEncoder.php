<?php

namespace RectorPrefix20210926;

if (\class_exists('t3lib_codec_JavaScriptEncoder')) {
    return;
}
class t3lib_codec_JavaScriptEncoder
{
}
\class_alias('t3lib_codec_JavaScriptEncoder', 't3lib_codec_JavaScriptEncoder', \false);
