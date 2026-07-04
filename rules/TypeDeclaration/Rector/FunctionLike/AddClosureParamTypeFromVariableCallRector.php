<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitor;
use PHPStan\Type\ObjectType;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeFromVariableCallRector\AddClosureParamTypeFromVariableCallRectorTest
 */
final class AddClosureParamTypeFromVariableCallRector extends AbstractRector
{
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add closure param object type based on the argument the assigned closure variable is called with', [new CodeSample(<<<'CODE_SAMPLE'
$printItem = function ($item) {
    echo $item->name;
};

$printItem(new Item());
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$printItem = function (Item $item) {
    echo $item->name;
};

$printItem(new Item());
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $closuresByVariableName = $this->collectAssignedClosures($node->stmts);
        if ($closuresByVariableName === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($closuresByVariableName as $variableName => $closure) {
            $argumentTypesByPosition = $this->collectCallArgumentTypes($node->stmts, $variableName);
            foreach ($closure->getParams() as $position => $param) {
                if (!isset($argumentTypesByPosition[$position])) {
                    continue;
                }
                if ($this->refactorClosureParam($param, $argumentTypesByPosition[$position])) {
                    $hasChanged = \true;
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param Node\Stmt[] $stmts
     * @return array<string, Closure>
     */
    private function collectAssignedClosures(array $stmts): array
    {
        $closuresByVariableName = [];
        $this->traverseNodesWithCallable($stmts, function (Node $node) use (&$closuresByVariableName): ?int {
            // do not descend into nested scopes
            if ($node instanceof Class_ || $node instanceof FunctionLike) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }
            if (!$node instanceof Assign) {
                return null;
            }
            if (!$node->var instanceof Variable) {
                return null;
            }
            if (!$node->expr instanceof Closure) {
                return null;
            }
            $variableName = $this->getName($node->var);
            if ($variableName === null) {
                return null;
            }
            $closuresByVariableName[$variableName] = $node->expr;
            return null;
        });
        return $closuresByVariableName;
    }
    /**
     * @param Node\Stmt[] $stmts
     * @return array<int, ObjectType>
     */
    private function collectCallArgumentTypes(array $stmts, string $variableName): array
    {
        $objectTypesByPosition = [];
        $blockedPositions = [];
        $this->traverseNodesWithCallable($stmts, function (Node $node) use ($variableName, &$objectTypesByPosition, &$blockedPositions): ?int {
            // do not descend into nested scopes, e.g. recursive self-calls
            if ($node instanceof Class_ || $node instanceof FunctionLike) {
                return NodeVisitor::DONT_TRAVERSE_CHILDREN;
            }
            if (!$node instanceof FuncCall || $node->isFirstClassCallable()) {
                return null;
            }
            if (!$node->name instanceof Variable || !$this->isName($node->name, $variableName)) {
                return null;
            }
            foreach ($node->getArgs() as $position => $arg) {
                if ($arg->unpack) {
                    $blockedPositions[$position] = \true;
                    continue;
                }
                $argType = $this->getType($arg->value);
                if (!$argType instanceof ObjectType) {
                    $blockedPositions[$position] = \true;
                    continue;
                }
                // conflicting object types across calls → skip this position
                if (isset($objectTypesByPosition[$position]) && $objectTypesByPosition[$position]->getClassName() !== $argType->getClassName()) {
                    $blockedPositions[$position] = \true;
                    continue;
                }
                $objectTypesByPosition[$position] = $argType;
            }
            return null;
        });
        foreach (array_keys($blockedPositions) as $position) {
            unset($objectTypesByPosition[$position]);
        }
        return $objectTypesByPosition;
    }
    private function refactorClosureParam(Param $param, ObjectType $objectType): bool
    {
        if ($param->type instanceof Node) {
            return \false;
        }
        if ($param->variadic) {
            return \false;
        }
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($objectType, TypeKind::PARAM);
        if (!$paramTypeNode instanceof Node) {
            return \false;
        }
        $param->type = $paramTypeNode;
        return \true;
    }
}
