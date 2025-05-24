<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\DefaultCollectionKeyRector\DefaultCollectionKeyRectorTest
 */
final class DefaultCollectionKeyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add default key to Collection generic type if missing in @param or @return of class method', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class ReturnSimpleCollection
{
    /**
     * @return Collection<string>
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class ReturnSimpleCollection
{
    /**
     * @return Collection<int, string>
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?ClassMethod
    {
        if ($node->isAbstract()) {
            return null;
        }
        $hasChanged = \false;
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($classMethodPhpDocInfo->getParamTagValueNodes() as $paramTagValueNode) {
            if ($this->processTagValueNode($paramTagValueNode)) {
                $hasChanged = \true;
            }
        }
        $returnTagValueNode = $classMethodPhpDocInfo->getReturnTagValue();
        if ($returnTagValueNode instanceof ReturnTagValueNode && $this->processTagValueNode($returnTagValueNode)) {
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode $tagValueNode
     */
    private function processTagValueNode($tagValueNode) : bool
    {
        if (!$tagValueNode->type instanceof GenericTypeNode) {
            return \false;
        }
        $genericTypeNode = $tagValueNode->type;
        if (\count($genericTypeNode->genericTypes) !== 1) {
            return \false;
        }
        if ($genericTypeNode->type->name !== 'Collection') {
            return \false;
        }
        $valueGenericType = $genericTypeNode->genericTypes[0];
        $genericTypeNode->genericTypes = [new IdentifierTypeNode('int'), $valueGenericType];
        return \true;
    }
}
