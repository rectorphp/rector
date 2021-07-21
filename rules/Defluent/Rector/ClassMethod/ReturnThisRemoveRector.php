<?php

declare (strict_types=1);
namespace Rector\Defluent\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Defluent\ConflictGuard\ParentClassMethodTypeOverrideGuard;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Defluent\Rector\ClassMethod\ReturnThisRemoveRector\ReturnThisRemoveRectorTest
 */
final class ReturnThisRemoveRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Defluent\ConflictGuard\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    public function __construct(\Rector\Defluent\ConflictGuard\ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes "return $this;" from *fluent interfaces* for specified classes.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeExampleClass
{
    public function someFunction()
    {
        return $this;
    }

    public function otherFunction()
    {
        return $this;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeExampleClass
{
    public function someFunction()
    {
    }

    public function otherFunction()
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $returnThis = $this->matchSingleReturnThis($node);
        if (!$returnThis instanceof \PhpParser\Node\Stmt\Return_) {
            return null;
        }
        if ($this->shouldSkip($returnThis, $node)) {
            return null;
        }
        $this->removeNode($returnThis);
        $classMethod = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::VOID_TYPE)) {
            $classMethod->returnType = new \PhpParser\Node\Identifier('void');
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $phpDocInfo->removeByType(\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode::class);
        return null;
    }
    /**
     * Matches only 1st level "return $this;"
     */
    private function matchSingleReturnThis(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\Return_
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf($classMethod, \PhpParser\Node\Stmt\Return_::class);
        // can be only 1 return
        if (\count($returns) !== 1) {
            return null;
        }
        $return = $returns[0];
        if (!$return->expr instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($return->expr, 'this')) {
            return null;
        }
        $parentNode = $return->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode !== $classMethod) {
            return null;
        }
        return $return;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\Return_ $return, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if ($this->parentClassMethodTypeOverrideGuard->hasParentMethodOutsideVendor($classMethod)) {
            return \true;
        }
        if ($return->expr === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $class = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$class instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        return $class->getMethod('__call') instanceof \PhpParser\Node\Stmt\ClassMethod;
    }
}
