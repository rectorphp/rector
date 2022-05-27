<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\v12\v0;

use RectorPrefix20220527\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Collector\RemoveTypoScriptStatementCollector;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class RemoveDisableCharsetHeaderConfigTypoScriptRector extends AbstractTypoScriptRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\FileProcessor\TypoScript\Collector\RemoveTypoScriptStatementCollector
     */
    private $removeTypoScriptStatementCollector;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(RemoveTypoScriptStatementCollector $removeTypoScriptStatementCollector, CurrentFileProvider $currentFileProvider)
    {
        $this->removeTypoScriptStatementCollector = $removeTypoScriptStatementCollector;
        $this->currentFileProvider = $currentFileProvider;
    }
    public function enterNode(Statement $statement) : void
    {
        if (!$statement instanceof Assignment) {
            return;
        }
        if ('config.disableCharsetHeader' !== $statement->object->absoluteName) {
            return;
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return;
        }
        $this->hasChanged = \true;
        $this->removeTypoScriptStatementCollector->removeStatement($statement, $file);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use array syntax for additionalHeaders', [new CodeSample(<<<'CODE_SAMPLE'
config.disableCharsetHeader = true
CODE_SAMPLE
, <<<'CODE_SAMPLE'
''
CODE_SAMPLE
)]);
    }
}
