<?php

namespace RectorPrefix20211020;

if (\interface_exists('Tx_Fluid_Core_Parser_InterceptorInterface')) {
    return;
}
interface Tx_Fluid_Core_Parser_InterceptorInterface
{
}
\class_alias('Tx_Fluid_Core_Parser_InterceptorInterface', 'Tx_Fluid_Core_Parser_InterceptorInterface', \false);
