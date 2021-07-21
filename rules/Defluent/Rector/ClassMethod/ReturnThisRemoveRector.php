<?php

declare(strict_types=1);

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
final class ReturnThisRemoveRector extends AbstractRector
{
    public function __construct(
        private ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes "return $this;" from *fluent interfaces* for specified classes.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $returnThis = $this->matchSingleReturnThis($node);
        if (! $returnThis instanceof Return_) {
            return null;
        }

        if ($this->shouldSkip($returnThis, $node)) {
            return null;
        }

        $this->removeNode($returnThis);

        $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof ClassMethod) {
            throw new ShouldNotHappenException();
        }

        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::VOID_TYPE)) {
            $classMethod->returnType = new Identifier('void');
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $phpDocInfo->removeByType(ReturnTagValueNode::class);

        return null;
    }

    /**
     * Matches only 1st level "return $this;"
     */
    private function matchSingleReturnThis(ClassMethod $classMethod): ?Return_
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf($classMethod, Return_::class);

        // can be only 1 return
        if (count($returns) !== 1) {
            return null;
        }

        $return = $returns[0];
        if (! $return->expr instanceof Variable) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($return->expr, 'this')) {
            return null;
        }

        $parentNode = $return->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode !== $classMethod) {
            return null;
        }

        return $return;
    }

    private function shouldSkip(Return_ $return, ClassMethod $classMethod): bool
    {
        if ($this->parentClassMethodTypeOverrideGuard->hasParentMethodOutsideVendor($classMethod)) {
            return true;
        }

        if ($return->expr === null) {
            throw new ShouldNotHappenException();
        }

        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof ClassLike) {
            return false;
        }

        return $class->getMethod('__call') instanceof ClassMethod;
    }
}
