<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Domain_Model_BackendUser')) {
    return;
}
class Tx_Extbase_Domain_Model_BackendUser
{
}
\class_alias('Tx_Extbase_Domain_Model_BackendUser', 'Tx_Extbase_Domain_Model_BackendUser', \false);
