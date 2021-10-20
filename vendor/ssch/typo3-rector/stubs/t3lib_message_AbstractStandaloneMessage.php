<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_message_AbstractStandaloneMessage')) {
    return;
}
class t3lib_message_AbstractStandaloneMessage
{
}
\class_alias('t3lib_message_AbstractStandaloneMessage', 't3lib_message_AbstractStandaloneMessage', \false);
