<?php

declare (strict_types=1);
namespace Rector\Naming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\MethodNameResolver;
use Rector\Naming\NodeRenamer\MethodCallRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector\MakeIsserClassMethodNameStartWithIsRectorTest
 */
final class MakeIsserClassMethodNameStartWithIsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @see https://regex101.com/r/Hc73ar/1
     * @var string
     */
    private const ISSER_NAME_REGEX = '#^(is|has|was|must|does|have|should|__)#';
    /**
     * @var MethodNameResolver
     */
    private $methodNameResolver;
    /**
     * @var MethodCallRenamer
     */
    private $methodCallRenamer;
    public function __construct(\Rector\Naming\Naming\MethodNameResolver $methodNameResolver, \Rector\Naming\NodeRenamer\MethodCallRenamer $methodCallRenamer)
    {
        $this->methodNameResolver = $methodNameResolver;
        $this->methodCallRenamer = $methodCallRenamer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change is method names to start with is/has/was', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var bool
     */
    private $isActive = false;

    public function getIsActive()
    {
        return $this->isActive;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var bool
     */
    private $isActive = false;

    public function isActive()
    {
        return $this->isActive;
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->isAlreadyIsserNamedClassMethod($node)) {
            return null;
        }
        $getterClassMethodReturnedExpr = $this->matchIsserClassMethodReturnedExpr($node);
        if (!$getterClassMethodReturnedExpr instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $isserMethodName = $this->methodNameResolver->resolveIsserFromReturnedExpr($getterClassMethodReturnedExpr);
        if ($isserMethodName === null) {
            return null;
        }
        if ($this->isName($node->name, $isserMethodName)) {
            return null;
        }
        $node->name = new \PhpParser\Node\Identifier($isserMethodName);
        $this->methodCallRenamer->updateClassMethodCalls($node, $isserMethodName);
        return $node;
    }
    private function isAlreadyIsserNamedClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        return $this->isName($classMethod, self::ISSER_NAME_REGEX);
    }
    private function matchIsserClassMethodReturnedExpr(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Expr
    {
        $stmts = (array) $classMethod->stmts;
        if (\count($stmts) !== 1) {
            return null;
        }
        $onlyStmt = $stmts[0] ?? null;
        if (!$onlyStmt instanceof \PhpParser\Node\Stmt\Return_) {
            return null;
        }
        if (!$onlyStmt->expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        $propertyStaticType = $this->getStaticType($onlyStmt->expr);
        if (!$propertyStaticType instanceof \PHPStan\Type\BooleanType) {
            return null;
        }
        return $onlyStmt->expr;
    }
}
