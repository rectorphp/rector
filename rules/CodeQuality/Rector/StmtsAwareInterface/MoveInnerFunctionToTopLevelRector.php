<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\StmtsAwareInterface\MoveInnerFunctionToTopLevelRector\MoveInnerFunctionToTopLevelRectorTest
 */
final class MoveInnerFunctionToTopLevelRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move an inner named function to the top level, as inner named functions are not supported by PHPStan', [new CodeSample(<<<'CODE_SAMPLE'
function outer(): void
{
    function inner(): void
    {
        echo 'hello';
    }

    inner();
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function inner(): void
{
    echo 'hello';
}

function outer(): void
{
    inner();
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactor(Node $node): ?Node
    {
        $stmts = (array) $node->stmts;
        $newStmts = [];
        $hasChanged = \false;
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Function_) {
                $newStmts[] = $stmt;
                continue;
            }
            $innerFunctions = $this->extractInnerFunctions($stmt, $stmts);
            foreach ($innerFunctions as $innerFunction) {
                $newStmts[] = $innerFunction;
                $hasChanged = \true;
            }
            $newStmts[] = $stmt;
        }
        if (!$hasChanged) {
            return null;
        }
        $node->stmts = $newStmts;
        return $node;
    }
    /**
     * @param Node\Stmt[] $siblingStmts
     * @return Function_[]
     */
    private function extractInnerFunctions(Function_ $outerFunction, array $siblingStmts): array
    {
        $innerFunctions = [];
        foreach ($outerFunction->stmts as $key => $innerStmt) {
            if (!$innerStmt instanceof Function_) {
                continue;
            }
            // avoid fatal error on name collision with an existing top-level function
            if ($this->hasSiblingFunctionOfSameName($innerStmt, $siblingStmts)) {
                continue;
            }
            $innerFunctions[] = $innerStmt;
            unset($outerFunction->stmts[$key]);
        }
        if ($innerFunctions !== []) {
            $outerFunction->stmts = array_values($outerFunction->stmts);
        }
        return $innerFunctions;
    }
    /**
     * @param Node\Stmt[] $siblingStmts
     */
    private function hasSiblingFunctionOfSameName(Function_ $innerFunction, array $siblingStmts): bool
    {
        $innerFunctionName = new Name($innerFunction->name->toString());
        foreach ($siblingStmts as $siblingStmt) {
            if (!$siblingStmt instanceof Function_) {
                continue;
            }
            if ($this->nodeNameResolver->areNamesEqual($siblingStmt, $innerFunction)) {
                return \true;
            }
            if ($this->reflectionProvider->hasFunction($innerFunctionName, null)) {
                $functionReflection = $this->reflectionProvider->getFunction($innerFunctionName, null);
                if ($functionReflection instanceof NativeFunctionReflection) {
                    return \true;
                }
            }
        }
        return \false;
    }
}
