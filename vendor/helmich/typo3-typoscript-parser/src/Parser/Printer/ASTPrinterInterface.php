<?php

declare (strict_types=1);
namespace RectorPrefix20210822\Helmich\TypoScriptParser\Parser\Printer;

use RectorPrefix20210822\Symfony\Component\Console\Output\OutputInterface;
interface ASTPrinterInterface
{
    /**
     * @param \RectorPrefix20210822\Helmich\TypoScriptParser\Parser\AST\Statement[]  $statements
     * @param \RectorPrefix20210822\Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    public function printStatements($statements, $output) : void;
    /**
     * @param PrettyPrinterConfiguration $prettyPrinterConfiguration
     */
    public function setPrettyPrinterConfiguration($prettyPrinterConfiguration) : void;
}
