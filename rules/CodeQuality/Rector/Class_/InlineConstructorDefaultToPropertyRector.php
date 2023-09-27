<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector\InlineConstructorDefaultToPropertyRectorTest
 */
final class InlineConstructorDefaultToPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move property default from constructor to property default', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private $name;

    public function __construct()
    {
        $this->name = 'John';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $name = 'John';

    public function __construct()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        if ($constructClassMethod->stmts === null) {
            return null;
        }
        foreach ($constructClassMethod->stmts as $key => $stmt) {
            // code that is possibly breaking flow
            if ($stmt instanceof If_) {
                return null;
            }
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            $propertyName = $this->matchAssignedLocalPropertyName($assign);
            if (!\is_string($propertyName)) {
                continue;
            }
            $defaultExpr = $assign->expr;
            if ($this->exprAnalyzer->isDynamicExpr($defaultExpr)) {
                continue;
            }
            $hasPropertyChanged = $this->refactorProperty($node, $propertyName, $defaultExpr, $constructClassMethod, $key);
            if ($hasPropertyChanged) {
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function matchAssignedLocalPropertyName(Assign $assign) : ?string
    {
        if (!$assign->var instanceof PropertyFetch) {
            return null;
        }
        $propertyFetch = $assign->var;
        if (!$this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
            return null;
        }
        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if (!\is_string($propertyName)) {
            return null;
        }
        return $propertyName;
    }
    private function refactorProperty(Class_ $class, string $propertyName, Expr $defaultExpr, ClassMethod $constructClassMethod, int $key) : bool
    {
        if ($class->isReadonly()) {
            return \false;
        }
        foreach ($class->stmts as $classStmt) {
            if (!$classStmt instanceof Property) {
                continue;
            }
            // readonly property cannot have default value
            if ($classStmt->isReadonly()) {
                continue;
            }
            foreach ($classStmt->props as $propertyProperty) {
                if (!$this->isName($propertyProperty, $propertyName)) {
                    continue;
                }
                $propertyProperty->default = $defaultExpr;
                // remove assign
                unset($constructClassMethod->stmts[$key]);
                return \true;
            }
        }
        return \false;
    }
}
