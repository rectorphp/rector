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
        foreach ($this->statementsToBeRemoved[$file->getFilePath()] as $sourceLine => $statementToBeRemoved) {
            if ($sourceLine === $originalStatement->sourceLine) {
                return \true;
            }
        }
        return \false;
    }
    public function reset() : void
    {
        $this->statementsToBeRemoved = [];
    }
}
