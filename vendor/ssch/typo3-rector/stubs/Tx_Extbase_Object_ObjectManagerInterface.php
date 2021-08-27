<?php

namespace RectorPrefix20210827;

if (\interface_exists('Tx_Extbase_Object_ObjectManagerInterface')) {
    return;
}
interface Tx_Extbase_Object_ObjectManagerInterface
{
}
\class_alias('Tx_Extbase_Object_ObjectManagerInterface', 'Tx_Extbase_Object_ObjectManagerInterface', \false);
