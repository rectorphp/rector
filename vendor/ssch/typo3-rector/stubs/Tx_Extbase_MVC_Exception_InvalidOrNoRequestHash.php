<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_MVC_Exception_InvalidOrNoRequestHash')) {
    return;
}
class Tx_Extbase_MVC_Exception_InvalidOrNoRequestHash
{
}
\class_alias('Tx_Extbase_MVC_Exception_InvalidOrNoRequestHash', 'Tx_Extbase_MVC_Exception_InvalidOrNoRequestHash', \false);
