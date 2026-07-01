<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\NarrowArrayCollectionUnionReturnDocblockRector\NarrowArrayCollectionUnionReturnDocblockRectorTest
 */
final class NarrowArrayCollectionUnionReturnDocblockRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @var string[]
     */
    private const COLLECTION_SHORT_NAMES = ['Collection', 'ArrayCollection'];
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change @return "Type[]|ArrayCollection" union docblock to a generic "ArrayCollection<int, Type>"', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;

class SomeClass
{
    /**
     * @return LeadEventLog[]|ArrayCollection
     */
    public function getSuccessful(): ArrayCollection
    {
        return $this->successful;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;

class SomeClass
{
    /**
     * @return ArrayCollection<int, LeadEventLog>
     */
    public function getSuccessful(): ArrayCollection
    {
        return $this->successful;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     * @return null|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    public function refactor(Node $node)
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return null;
        }
        if (!$returnTagValueNode->type instanceof UnionTypeNode) {
            return null;
        }
        $genericTypeNode = $this->matchGenericCollectionTypeNode($returnTagValueNode->type);
        if (!$genericTypeNode instanceof GenericTypeNode) {
            return null;
        }
        $returnTagValueNode->type = $genericTypeNode;
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    private function matchGenericCollectionTypeNode(UnionTypeNode $unionTypeNode): ?GenericTypeNode
    {
        if (count($unionTypeNode->types) !== 2) {
            return null;
        }
        $arrayItemTypeNode = null;
        $collectionIdentifierTypeNode = null;
        foreach ($unionTypeNode->types as $typeNode) {
            if ($typeNode instanceof ArrayTypeNode && $typeNode->type instanceof IdentifierTypeNode) {
                $arrayItemTypeNode = $typeNode->type;
                continue;
            }
            if ($typeNode instanceof IdentifierTypeNode && $this->isCollectionIdentifier($typeNode)) {
                $collectionIdentifierTypeNode = $typeNode;
            }
        }
        if (!$arrayItemTypeNode instanceof IdentifierTypeNode) {
            return null;
        }
        if (!$collectionIdentifierTypeNode instanceof IdentifierTypeNode) {
            return null;
        }
        $genericTypeNodes = [new IdentifierTypeNode('int'), $arrayItemTypeNode];
        return new GenericTypeNode($collectionIdentifierTypeNode, $genericTypeNodes);
    }
    private function isCollectionIdentifier(IdentifierTypeNode $identifierTypeNode): bool
    {
        $shortName = $this->resolveShortName($identifierTypeNode->name);
        return in_array($shortName, self::COLLECTION_SHORT_NAMES, \true);
    }
    private function resolveShortName(string $name): string
    {
        $name = ltrim($name, '\\');
        $lastBackslashPosition = strrpos($name, '\\');
        if ($lastBackslashPosition === \false) {
            return $name;
        }
        return (string) substr($name, $lastBackslashPosition + 1);
    }
}
