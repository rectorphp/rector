<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_mail_MailerAdapter')) {
    return;
}
class t3lib_mail_MailerAdapter
{
}
\class_alias('t3lib_mail_MailerAdapter', 't3lib_mail_MailerAdapter', \false);
