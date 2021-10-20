<?php

namespace RectorPrefix20211020;

if (\interface_exists('Tx_Fluid_Core_Parser_ParsedTemplateInterface')) {
    return;
}
interface Tx_Fluid_Core_Parser_ParsedTemplateInterface
{
}
\class_alias('Tx_Fluid_Core_Parser_ParsedTemplateInterface', 'Tx_Fluid_Core_Parser_ParsedTemplateInterface', \false);
