<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveVoidDocblockFromMagicMethodRector\RemoveVoidDocblockFromMagicMethodRectorTest
 */
final class RemoveVoidDocblockFromMagicMethodRector extends AbstractRector
{
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove useless @return void docblock from magic methods __construct, __destruct, and __clone', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return void
     */
    public function __construct() {}

    /**
     * @return void
     */
    public function __destruct() {}

    /**
     * @return void
     */
    public function __clone() {}
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct() {}

    public function __destruct() {}

    public function __clone() {}
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->returnType instanceof Node) {
            return null;
        }
        if (!$this->isNames($node, [MethodName::CONSTRUCT, MethodName::DESTRUCT, MethodName::CLONE])) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return null;
        }
        if ($returnTagValueNode->description !== '') {
            return null;
        }
        if (!$returnTagValueNode->type instanceof IdentifierTypeNode || $returnTagValueNode->type->name !== 'void') {
            return null;
        }
        $phpDocInfo->removeByType(ReturnTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
}
