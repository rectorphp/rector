<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Collector;

use Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
final class RemoveTypoScriptStatementCollector
{
    /**
     * @var Statement[][]
     */
    private $statementsToBeRemoved = [];
    public function removeStatement(Statement $statement, File $file) : void
    {
        $filePath = $file->getFilePath();
        if (!isset($this->statementsToBeRemoved[$filePath])) {
            $this->statementsToBeRemoved[$filePath] = [];
        }
        $this->statementsToBeRemoved[$filePath][$statement->sourceLine] = $statement;
    }
    public function shouldStatementBeRemoved(Statement $originalStatement, File $file) : bool
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
