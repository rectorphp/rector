<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\TryCatch;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/multiple-catch
 *
 * @see \Rector\Tests\Php71\Rector\TryCatch\MultiExceptionCatchRector\MultiExceptionCatchRectorTest
 */
final class MultiExceptionCatchRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(\Rector\Core\Contract\PhpParser\NodePrinterInterface $nodePrinter)
    {
        $this->nodePrinter = $nodePrinter;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes multi catch of same exception to single one | separated.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
try {
    // Some code...
} catch (ExceptionType1 $exception) {
    $sameCode;
} catch (ExceptionType2 $exception) {
    $sameCode;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
try {
    // Some code...
} catch (ExceptionType1 | ExceptionType2 $exception) {
    $sameCode;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\TryCatch::class];
    }
    /**
     * @param TryCatch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (\count($node->catches) < 2) {
            return null;
        }
        $catchKeysByContent = $this->collectCatchKeysByContent($node);
        /** @var Catch_[] $catchKeys */
        foreach ($catchKeysByContent as $catchKeys) {
            // no duplicates
            $count = \count($catchKeys);
            if ($count < 2) {
                continue;
            }
            $collectedTypes = $this->collectTypesFromCatchedByIds($catchKeys);
            /** @var Catch_ $firstCatch */
            $firstCatch = \array_shift($catchKeys);
            $firstCatch->types = $collectedTypes;
            foreach ($catchKeys as $catchKey) {
                $this->removeNode($catchKey);
            }
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::MULTI_EXCEPTION_CATCH;
    }
    /**
     * @return array<string, Catch_[]>
     */
    private function collectCatchKeysByContent(\PhpParser\Node\Stmt\TryCatch $tryCatch) : array
    {
        $catchKeysByContent = [];
        foreach ($tryCatch->catches as $catch) {
            $catchContent = $this->nodePrinter->print($catch->stmts);
            $catchKeysByContent[$catchContent][] = $catch;
        }
        return $catchKeysByContent;
    }
    /**
     * @param Catch_[] $catches
     * @return Name[]
     */
    private function collectTypesFromCatchedByIds(array $catches) : array
    {
        $collectedTypes = [];
        foreach ($catches as $catch) {
            $collectedTypes = \array_merge($collectedTypes, $catch->types);
        }
        return $collectedTypes;
    }
}
