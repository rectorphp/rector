<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Collector;

use Helmich\TypoScriptParser\Parser\AST\Statement;
use Rector\Core\ValueObject\Application\File;
final class RemoveTypoScriptStatementCollector
{
    /**
     * @var Statement[][]
     */
    private $statementsToBeRemoved = [];
    public function removeStatement(\Helmich\TypoScriptParser\Parser\AST\Statement $statement, \Rector\Core\ValueObject\Application\File $file) : void
    {
        $filePath = $file->getFilePath();
        if (!isset($this->statementsToBeRemoved[$filePath])) {
            $this->statementsToBeRemoved[$filePath] = [];
        }
        $this->statementsToBeRemoved[$filePath][$statement->sourceLine] = $statement;
    }
    public function shouldStatementBeRemoved(\Helmich\TypoScriptParser\Parser\AST\Statement $originalStatement, \Rector\Core\ValueObject\Application\File $file) : bool
    {
        if (!isset($this->statementsToBeRemoved[$file->getFilePath()])) {
            return \false;
        }
        $desiredSourceLine = $originalStatement->sourceLine;
        $currentFileStatements = $this->statementsToBeRemoved[$file->getFilePath()];
        return \array_key_exists($desiredSourceLine, $currentFileStatements);
    }
    public function reset() : void
    {
        $this->statementsToBeRemoved = [];
    }
}
