<?php

namespace RectorPrefix20210916;

if (\class_exists('t3lib_mail_Mailer')) {
    return;
}
class t3lib_mail_Mailer
{
}
\class_alias('t3lib_mail_Mailer', 't3lib_mail_Mailer', \false);
