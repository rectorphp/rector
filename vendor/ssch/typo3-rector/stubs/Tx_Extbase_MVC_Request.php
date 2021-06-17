<?php

namespace RectorPrefix20210617;

if (\class_exists('Tx_Extbase_MVC_Request')) {
    return;
}
class Tx_Extbase_MVC_Request
{
}
\class_alias('Tx_Extbase_MVC_Request', 'Tx_Extbase_MVC_Request', \false);
