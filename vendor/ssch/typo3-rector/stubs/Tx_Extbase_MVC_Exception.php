<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_MVC_Exception')) {
    return;
}
class Tx_Extbase_MVC_Exception
{
}
\class_alias('Tx_Extbase_MVC_Exception', 'Tx_Extbase_MVC_Exception', \false);
