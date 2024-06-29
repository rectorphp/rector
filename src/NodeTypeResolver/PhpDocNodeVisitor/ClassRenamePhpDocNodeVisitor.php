<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDocNodeVisitor;

use PhpParser\Node as PhpNode;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Exception\ShouldNotHappenException;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
use Rector\Renaming\Collector\RenamedNameCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class ClassRenamePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @readonly
     * @var \Rector\Renaming\Collector\RenamedNameCollector
     */
    private $renamedNameCollector;
    /**
     * @var OldToNewType[]
     */
    private $oldToNewTypes = [];
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @var PhpNode|null
     */
    private $currentPhpNode;
    public function __construct(StaticTypeMapper $staticTypeMapper, UseImportsResolver $useImportsResolver, RenamedNameCollector $renamedNameCollector)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->useImportsResolver = $useImportsResolver;
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
        $virtualNode = $currentPhpNode->getAttribute(AttributeKey::VIRTUAL_NODE);
        if ($virtualNode === \true) {
            return null;
        }
        $identifier = clone $node;
        $identifier->name = $this->resolveNamespacedName($identifier, $currentPhpNode, $node->name);
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($identifier, $currentPhpNode);
        // make sure to compare FQNs
        $objectType = $this->expandShortenedObjectType($staticType);
        foreach ($this->oldToNewTypes as $oldToNewType) {
            /** @var ObjectType $oldType */
            $oldType = $oldToNewType->getOldType();
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
    private function resolveNamespacedName(IdentifierTypeNode $identifierTypeNode, PhpNode $phpNode, string $name) : string
    {
        if (\strncmp($name, '\\', \strlen('\\')) === 0) {
            return $name;
        }
        if (\strpos($name, '\\') !== \false) {
            return $name;
        }
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($identifierTypeNode, $phpNode);
        if (!$staticType instanceof ObjectType) {
            return $name;
        }
        if ($staticType instanceof ShortenedObjectType) {
            return $name;
        }
        $uses = $this->useImportsResolver->resolve();
        $originalNode = $phpNode->getAttribute(AttributeKey::ORIGINAL_NODE);
        $scope = $originalNode instanceof PhpNode ? $originalNode->getAttribute(AttributeKey::SCOPE) : $phpNode->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            if (!$originalNode instanceof PhpNode) {
                return $this->resolveNamefromUse($uses, $name);
            }
            return '';
        }
        $namespaceName = $scope->getNamespace();
        if ($namespaceName === null) {
            return $this->resolveNamefromUse($uses, $name);
        }
        if ($uses === []) {
            return $namespaceName . '\\' . $name;
        }
        $nameFromUse = $this->resolveNamefromUse($uses, $name);
        if ($nameFromUse !== $name) {
            return $nameFromUse;
        }
        return $namespaceName . '\\' . $nameFromUse;
    }
    /**
     * @param array<Use_|GroupUse> $uses
     */
    private function resolveNamefromUse(array $uses, string $name) : string
    {
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            foreach ($use->uses as $useUse) {
                if ($useUse->alias instanceof Identifier) {
                    continue;
                }
                $lastName = $useUse->name->getLast();
                if ($lastName === $name) {
                    return $prefix . $useUse->name->toString();
                }
            }
        }
        return $name;
    }
    /**
     * @return \PHPStan\Type\ObjectType|\PHPStan\Type\Type
     */
    private function expandShortenedObjectType(Type $type)
    {
        if ($type instanceof ShortenedObjectType) {
            return new ObjectType($type->getFullyQualifiedName());
        }
        return $type;
    }
}
