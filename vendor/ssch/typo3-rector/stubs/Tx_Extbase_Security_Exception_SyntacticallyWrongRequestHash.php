<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Security_Exception_SyntacticallyWrongRequestHash')) {
    return;
}
class Tx_Extbase_Security_Exception_SyntacticallyWrongRequestHash
{
}
\class_alias('Tx_Extbase_Security_Exception_SyntacticallyWrongRequestHash', 'Tx_Extbase_Security_Exception_SyntacticallyWrongRequestHash', \false);
