<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector;

use RectorPrefix20220418\Helmich\TypoScriptParser\Parser\AST\FileIncludeStatement;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Feature-82812-NewSyntaxForImportingTypoScriptFiles.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\TypoScript\TypoScriptProcessorTest
 */
final class FileIncludeToImportStatementTypoScriptRector extends \Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector
{
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(\Rector\Core\Provider\CurrentFileProvider $currentFileProvider)
    {
        $this->currentFileProvider = $currentFileProvider;
    }
    public function enterNode(\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
        if (!$statement instanceof \RectorPrefix20220418\Helmich\TypoScriptParser\Parser\AST\FileIncludeStatement) {
            return;
        }
        if (null !== $statement->condition) {
            return;
        }
        if ($statement->newSyntax) {
            return;
        }
        $file = $this->currentFileProvider->getFile();
        if ($file instanceof \Rector\Core\ValueObject\Application\File) {
            $file->addRectorClassWithLine(new \Rector\ChangesReporting\ValueObject\RectorWithLineChange($this, $statement->sourceLine));
        }
        $this->hasChanged = \true;
        $statement->newSyntax = \true;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert old include statement to new import syntax', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
<INCLUDE_TYPOSCRIPT: source="FILE:conditions.typoscript">
CODE_SAMPLE
, <<<'CODE_SAMPLE'
@import conditions.typoscript
CODE_SAMPLE
)]);
    }
}
