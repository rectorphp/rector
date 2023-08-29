<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictParamRector\ReturnTypeFromStrictParamRectorTest
 */
final class ReturnTypeFromStrictParamRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnTypeInferer $returnTypeInferer)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnTypeInferer = $returnTypeInferer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type based on strict parameter type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve(ParamType $item)
    {
        return $item;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve(ParamType $item): ParamType
    {
        return $item;
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
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULLABLE_TYPE;
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        if ($this->shouldSkipNode($node, $scope)) {
            return null;
        }
        $return = $this->findCurrentScopeReturn($node->stmts);
        if (!$return instanceof Return_ || !$return->expr instanceof Expr) {
            return null;
        }
        $returnName = $this->getName($return->expr);
        $stmts = $node->stmts;
        foreach ($node->getParams() as $param) {
            if (!$param->type instanceof Node) {
                continue;
            }
            if ($this->shouldSkipParam($param, $stmts)) {
                continue;
            }
            $paramName = $this->getName($param);
            if ($returnName !== $paramName) {
                continue;
            }
            $node->returnType = $param->type;
            return $node;
        }
        return null;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function findCurrentScopeReturn(array $stmts) : ?Return_
    {
        $return = null;
        $this->traverseNodesWithCallable($stmts, static function (Node $node) use(&$return) : ?int {
            // skip scope nesting
            if ($node instanceof Class_ || $node instanceof FunctionLike) {
                $return = null;
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Return_) {
                return null;
            }
            if (!$node->expr instanceof Variable) {
                $return = null;
                return NodeTraverser::STOP_TRAVERSAL;
            }
            $return = $node;
            return null;
        });
        return $return;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function shouldSkipParam(Param $param, array $stmts) : bool
    {
        $paramName = $this->getName($param);
        $isParamModified = \false;
        $this->traverseNodesWithCallable($stmts, function (Node $node) use($paramName, &$isParamModified) : ?int {
            // skip scope nesting
            if ($node instanceof Class_ || $node instanceof FunctionLike) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($node instanceof AssignRef && $this->isName($node->expr, $paramName)) {
                $isParamModified = \true;
                return NodeTraverser::STOP_TRAVERSAL;
            }
            if (!$node instanceof Assign) {
                return null;
            }
            if (!$node->var instanceof Variable) {
                return null;
            }
            if (!$this->isName($node->var, $paramName)) {
                return null;
            }
            $isParamModified = \true;
            return NodeTraverser::STOP_TRAVERSAL;
        });
        return $isParamModified;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function shouldSkipNode($node, Scope $scope) : bool
    {
        if ($node->returnType !== null) {
            return \true;
        }
        if ($node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return \true;
        }
        $returnType = $this->returnTypeInferer->inferFunctionLike($node);
        if ($returnType instanceof MixedType) {
            return \true;
        }
        $returnType = TypeCombinator::removeNull($returnType);
        return $returnType instanceof UnionType;
    }
}
