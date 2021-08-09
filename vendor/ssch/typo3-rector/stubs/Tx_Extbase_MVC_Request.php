<?php

namespace RectorPrefix20210809;

if (\class_exists('Tx_Extbase_MVC_Request')) {
    return;
}
class Tx_Extbase_MVC_Request
{
}
\class_alias('Tx_Extbase_MVC_Request', 'Tx_Extbase_MVC_Request', \false);
