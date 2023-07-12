<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use Rector\Core\Rector\AbstractRector;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector\StrictArrayParamDimFetchRectorTest
 */
final class StrictArrayParamDimFetchRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    public function __construct(ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add array type based on array dim fetch use', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve($item)
    {
        return $item['name'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve(array $item)
    {
        return $item['name'];
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
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        if ($node instanceof ClassMethod && $this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($node)) {
            return null;
        }
        foreach ($node->getParams() as $param) {
            if ($param->type instanceof Node) {
                continue;
            }
            if (!$this->isParamAccessedArrayDimFetch($param, $node)) {
                continue;
            }
            $param->type = new Identifier('array');
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function isParamAccessedArrayDimFetch(Param $param, $functionLike) : bool
    {
        $paramName = $this->getName($param);
        $isParamAccessedArrayDimFetch = \false;
        $this->traverseNodesWithCallable($functionLike, function (Node $node) use($paramName, &$isParamAccessedArrayDimFetch) : ?int {
            if ($node instanceof FuncCall && $this->isNames($node, ['is_array', 'is_string', 'is_int', 'is_bool', 'is_float'])) {
                $firstArg = $node->getArgs()[0];
                if ($this->isName($firstArg->value, $paramName)) {
                    return NodeTraverser::STOP_TRAVERSAL;
                }
            }
            if (!$node instanceof ArrayDimFetch) {
                return null;
            }
            if (!$node->var instanceof Variable) {
                return null;
            }
            if (!$this->isName($node->var, $paramName)) {
                return null;
            }
            $isParamAccessedArrayDimFetch = \true;
            return null;
        });
        return $isParamAccessedArrayDimFetch;
    }
}
