<?php

namespace RectorPrefix20210827;

if (\class_exists('Tx_Fluid_Core_Parser_Interceptor_Escape')) {
    return;
}
class Tx_Fluid_Core_Parser_Interceptor_Escape
{
}
\class_alias('Tx_Fluid_Core_Parser_Interceptor_Escape', 'Tx_Fluid_Core_Parser_Interceptor_Escape', \false);
