<?php

namespace RectorPrefix20211110;

if (\class_exists('t3lib_mail_Message')) {
    return;
}
class t3lib_mail_Message
{
}
\class_alias('t3lib_mail_Message', 't3lib_mail_Message', \false);
