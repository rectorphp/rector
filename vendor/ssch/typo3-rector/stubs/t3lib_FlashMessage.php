<?php

namespace RectorPrefix20210827;

if (\class_exists('t3lib_FlashMessage')) {
    return;
}
class t3lib_FlashMessage
{
}
\class_alias('t3lib_FlashMessage', 't3lib_FlashMessage', \false);
