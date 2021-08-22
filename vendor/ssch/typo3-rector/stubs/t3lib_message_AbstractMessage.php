<?php

namespace RectorPrefix20210822;

if (\class_exists('t3lib_message_AbstractMessage')) {
    return;
}
class t3lib_message_AbstractMessage
{
}
\class_alias('t3lib_message_AbstractMessage', 't3lib_message_AbstractMessage', \false);
