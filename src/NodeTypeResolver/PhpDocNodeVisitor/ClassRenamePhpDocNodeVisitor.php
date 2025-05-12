<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDocNodeVisitor;

use PhpParser\Node as PhpNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Generic\TemplateObjectType;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
use Rector\Renaming\Collector\RenamedNameCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class ClassRenamePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private RenamedNameCollector $renamedNameCollector;
    /**
     * @var OldToNewType[]
     */
    private array $oldToNewTypes = [];
    private bool $hasChanged = \false;
    private ?PhpNode $currentPhpNode = null;
    public function __construct(StaticTypeMapper $staticTypeMapper, RenamedNameCollector $renamedNameCollector)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->renamedNameCollector = $renamedNameCollector;
    }
    public function setCurrentPhpNode(PhpNode $phpNode) : void
    {
        $this->currentPhpNode = $phpNode;
    }
    public function beforeTraverse(Node $node) : void
    {
        if ($this->oldToNewTypes === []) {
            throw new ShouldNotHappenException('Configure "$oldToNewClasses" first');
        }
        if (!$this->currentPhpNode instanceof PhpNode) {
            throw new ShouldNotHappenException('Configure "$currentPhpNode" first');
        }
        $this->hasChanged = \false;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof IdentifierTypeNode) {
            return null;
        }
        /** @var \PhpParser\Node $currentPhpNode */
        $currentPhpNode = $this->currentPhpNode;
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $currentPhpNode);
        // non object type and @template is to not be renamed
        if (!$staticType instanceof ObjectType || $staticType instanceof TemplateObjectType) {
            return null;
        }
        // make sure to compare FQNs
        $objectType = $this->ensureFQCNObject($staticType, $node->name);
        foreach ($this->oldToNewTypes as $oldToNewType) {
            $oldType = $oldToNewType->getOldType();
            if (!$oldType instanceof ObjectType) {
                continue;
            }
            if (!$objectType->equals($oldType)) {
                continue;
            }
            $newTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($oldToNewType->getNewType());
            $parentType = $node->getAttribute(PhpDocAttributeKey::PARENT);
            if ($parentType instanceof TypeNode) {
                // mirror attributes
                $newTypeNode->setAttribute(PhpDocAttributeKey::PARENT, $parentType);
            }
            $this->hasChanged = \true;
            $this->renamedNameCollector->add($oldType->getClassName());
            return $newTypeNode;
        }
        return null;
    }
    /**
     * @param OldToNewType[] $oldToNewTypes
     */
    public function setOldToNewTypes(array $oldToNewTypes) : void
    {
        $this->oldToNewTypes = $oldToNewTypes;
    }
    public function hasChanged() : bool
    {
        return $this->hasChanged;
    }
    private function ensureFQCNObject(ObjectType $objectType, string $identifierName) : ObjectType
    {
        if ($objectType instanceof ShortenedObjectType && \strncmp($identifierName, '\\', \strlen('\\')) === 0) {
            return new ObjectType(\ltrim($identifierName, '\\'));
        }
        if ($objectType instanceof ShortenedObjectType || $objectType instanceof AliasedObjectType) {
            return new ObjectType($objectType->getFullyQualifiedName());
        }
        return $objectType;
    }
}
