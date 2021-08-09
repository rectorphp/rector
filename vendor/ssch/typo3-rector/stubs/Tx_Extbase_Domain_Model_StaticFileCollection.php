<?php

namespace RectorPrefix20210809;

if (\class_exists('Tx_Extbase_Domain_Model_StaticFileCollection')) {
    return;
}
class Tx_Extbase_Domain_Model_StaticFileCollection
{
}
\class_alias('Tx_Extbase_Domain_Model_StaticFileCollection', 'Tx_Extbase_Domain_Model_StaticFileCollection', \false);
