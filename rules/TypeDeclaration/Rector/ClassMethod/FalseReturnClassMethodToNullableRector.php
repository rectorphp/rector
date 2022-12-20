<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\Matcher\ReturnFalseAndReturnOtherMatcher;
use Rector\TypeDeclaration\ValueObject\ReturnFalseAndReturnOther;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\FalseReturnClassMethodToNullableRector\FalseReturnClassMethodToNullableRectorTest
 */
final class FalseReturnClassMethodToNullableRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\Matcher\ReturnFalseAndReturnOtherMatcher
     */
    private $returnFalseAndReturnOtherMatcher;
    public function __construct(ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnFalseAndReturnOtherMatcher $returnFalseAndReturnOtherMatcher)
    {
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnFalseAndReturnOtherMatcher = $returnFalseAndReturnOtherMatcher;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change class method that returns false as invalid state, to nullable', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return false|int
     */
    public function run(int $number)
    {
        if ($number === 10) {
            return false;
        }

        return $number;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $number): ?int
    {
        if ($number === 10) {
            return null;
        }

        return $number;
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        // it already has a type
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node)) {
            return null;
        }
        $returnFalseAndReturnOther = $this->returnFalseAndReturnOtherMatcher->match($node);
        if (!$returnFalseAndReturnOther instanceof ReturnFalseAndReturnOther) {
            return null;
        }
        $falseReturn = $returnFalseAndReturnOther->getFalseReturn();
        $otherReturn = $returnFalseAndReturnOther->getOtherReturn();
        $anotherType = $this->getType($otherReturn->expr);
        if ($anotherType instanceof MixedType) {
            return null;
        }
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($anotherType, TypeKind::RETURN);
        if (!$typeNode instanceof Name && !$typeNode instanceof Identifier) {
            return null;
        }
        $node->returnType = new NullableType($typeNode);
        $this->clearReturnPhpDocTagNode($node);
        // update return node
        $falseReturn->expr = $this->nodeFactory->createNull();
        return $node;
    }
    private function clearReturnPhpDocTagNode(ClassMethod $classMethod) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return;
        }
        if ($returnTagValueNode->description !== '') {
            // keep as useful
            return;
        }
        $phpDocInfo->removeByType(ReturnTagValueNode::class);
    }
}
