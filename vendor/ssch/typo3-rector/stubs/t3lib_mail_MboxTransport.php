<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_mail_MboxTransport')) {
    return;
}
class t3lib_mail_MboxTransport
{
}
\class_alias('t3lib_mail_MboxTransport', 't3lib_mail_MboxTransport', \false);
