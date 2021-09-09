<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_Domain_Model_FrontendUser')) {
    return;
}
class Tx_Extbase_Domain_Model_FrontendUser
{
}
\class_alias('Tx_Extbase_Domain_Model_FrontendUser', 'Tx_Extbase_Domain_Model_FrontendUser', \false);
