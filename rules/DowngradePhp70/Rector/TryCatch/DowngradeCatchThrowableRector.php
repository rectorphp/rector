<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\TryCatch;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Throwable was introduced in PHP 7.0 so to support older versions we need to also check for Exception.
 * @changelog https://www.php.net/manual/en/class.throwable.php
 * @see \Rector\Tests\DowngradePhp70\Rector\TryCatch\DowngradeCatchThrowableRector\DowngradeCatchThrowableRectorTest
 */
final class DowngradeCatchThrowableRector extends AbstractRector
{
    /**
     * @var string
     */
    private const EXCEPTION = 'Exception';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make catch clauses catching `Throwable` also catch `Exception` to support exception hierarchies in PHP 5.', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [TryCatch::class];
    }
    /**
     * @param TryCatch $node
     */
    public function refactor(Node $node) : ?Node
    {
        $originalCatches = $node->catches;
        $hasChanged = \false;
        foreach ($node->catches as $catch) {
            if (!$this->shouldAddExceptionFallback($catch, $node)) {
                continue;
            }
            $catchType = new FullyQualified(self::EXCEPTION);
            $exceptionCatch = new Catch_([$catchType], $catch->var, $catch->stmts);
            $originalCatches[] = $exceptionCatch;
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        $node->catches = $originalCatches;
        return $node;
    }
    /**
     * @param Name[] $types
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
     * @param Stmt[] $body
     * @param Catch_[] $catches
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
    private function shouldAddExceptionFallback(Catch_ $catch, TryCatch $tryCatch) : bool
    {
        if (!$this->isCatchingType($catch->types, 'Throwable')) {
            return \false;
        }
        if ($this->isCatchingType($catch->types, self::EXCEPTION)) {
            return \false;
        }
        return !$this->isCaughtByAnotherClause($catch->stmts, $tryCatch->catches);
    }
}
