<?php

namespace RectorPrefix20210622;

if (\class_exists('Tx_Extbase_MVC_Dispatcher')) {
    return;
}
class Tx_Extbase_MVC_Dispatcher
{
}
\class_alias('Tx_Extbase_MVC_Dispatcher', 'Tx_Extbase_MVC_Dispatcher', \false);
