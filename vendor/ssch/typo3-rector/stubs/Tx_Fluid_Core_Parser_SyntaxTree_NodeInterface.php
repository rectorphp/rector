<?php

namespace RectorPrefix20210902;

if (\interface_exists('Tx_Fluid_Core_Parser_SyntaxTree_NodeInterface')) {
    return;
}
interface Tx_Fluid_Core_Parser_SyntaxTree_NodeInterface
{
}
\class_alias('Tx_Fluid_Core_Parser_SyntaxTree_NodeInterface', 'Tx_Fluid_Core_Parser_SyntaxTree_NodeInterface', \false);
