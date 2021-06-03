<?php

namespace RectorPrefix20210603;

if (\class_exists('t3lib_FlashMessageQueue')) {
    return;
}
class t3lib_FlashMessageQueue
{
}
\class_alias('t3lib_FlashMessageQueue', 't3lib_FlashMessageQueue', \false);
