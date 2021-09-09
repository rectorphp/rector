<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_MVC_CLI_RequestHandler')) {
    return;
}
class Tx_Extbase_MVC_CLI_RequestHandler
{
}
\class_alias('Tx_Extbase_MVC_CLI_RequestHandler', 'Tx_Extbase_MVC_CLI_RequestHandler', \false);
