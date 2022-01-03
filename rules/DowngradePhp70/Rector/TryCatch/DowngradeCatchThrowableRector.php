<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\TryCatch;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Throwable was introduced in PHP 7.0 so to support older versions we need to also check for Exception.
 * @see https://www.php.net/manual/en/class.throwable.php
 * @see \Rector\Tests\DowngradePhp70\Rector\TryCatch\DowngradeCatchThrowableRector\DowngradeCatchThrowableRectorTest
 */
final class DowngradeCatchThrowableRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const EXCEPTION = 'Exception';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Make catch clauses catching `Throwable` also catch `Exception` to support exception hierarchies in PHP 5.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
try {
    // Some code...
} catch (\Throwable $exception) {
    handle();
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
try {
    // Some code...
} catch (\Throwable $exception) {
    handle();
} catch (\Exception $exception) {
    handle();
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
        $originalCatches = $node->catches;
        foreach ($node->catches as $key => $catch) {
            $shouldAddExceptionFallback = $this->isCatchingType($catch->types, 'Throwable') && !$this->isCatchingType($catch->types, self::EXCEPTION) && !$this->isCaughtByAnotherClause($catch->stmts, $node->catches);
            if ($shouldAddExceptionFallback) {
                $catchType = new \PhpParser\Node\Name\FullyQualified(self::EXCEPTION);
                $this->nodesToAddCollector->addNodeAfterNode(new \PhpParser\Node\Stmt\Catch_([$catchType], $catch->var, $catch->stmts), $node->catches[$key]);
            }
        }
        if ($this->nodeComparator->areNodesEqual($node->catches, $originalCatches)) {
            return null;
        }
        return $node;
    }
    /**
     * @param Node\Name[] $types
     */
    private function isCatchingType(array $types, string $expected) : bool
    {
        foreach ($types as $type) {
            if ($this->nodeNameResolver->isName($type, $expected)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Node\Stmt[] $body
     * @param Node\Stmt\Catch_[] $catches
     */
    private function isCaughtByAnotherClause(array $body, array $catches) : bool
    {
        foreach ($catches as $catch) {
            $caughtAndBodyMatches = $this->isCatchingType($catch->types, self::EXCEPTION) && $this->nodeComparator->areNodesEqual($catch->stmts, $body);
            if ($caughtAndBodyMatches) {
                return \true;
            }
        }
        return \false;
    }
}
