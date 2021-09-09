<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_MVC_Web_AbstractRequestHandler')) {
    return;
}
class Tx_Extbase_MVC_Web_AbstractRequestHandler
{
}
\class_alias('Tx_Extbase_MVC_Web_AbstractRequestHandler', 'Tx_Extbase_MVC_Web_AbstractRequestHandler', \false);
