<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PhpDocNodeVisitor;

use PhpParser\Node as PhpParserNode;
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
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
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
     * @var \Rector\Core\Configuration\CurrentNodeProvider
     */
    private $currentNodeProvider;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @var OldToNewType[]
     */
    private $oldToNewTypes = [];
    public function __construct(StaticTypeMapper $staticTypeMapper, CurrentNodeProvider $currentNodeProvider, UseImportsResolver $useImportsResolver)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->useImportsResolver = $useImportsResolver;
    }
    public function beforeTraverse(Node $node) : void
    {
        if ($this->oldToNewTypes === []) {
            throw new ShouldNotHappenException('Configure "$oldToNewClasses" first');
        }
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof IdentifierTypeNode) {
            return null;
        }
        $phpParserNode = $this->currentNodeProvider->getNode();
        if (!$phpParserNode instanceof PhpParserNode) {
            throw new ShouldNotHappenException();
        }
        $virtualNode = $phpParserNode->getAttribute(AttributeKey::VIRTUAL_NODE);
        if ($virtualNode === \true) {
            return null;
        }
        $identifier = clone $node;
        $identifier->name = $this->resolveNamespacedName($identifier, $phpParserNode, $node->name);
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($identifier, $phpParserNode);
        $shouldImport = SimpleParameterProvider::provideBoolParameter(Option::AUTO_IMPORT_NAMES);
        $isNoNamespacedName = \strncmp($identifier->name, '\\', \strlen('\\')) !== 0 && \substr_count($identifier->name, '\\') === 0;
        // tweak overlapped import + rename
        if ($shouldImport && $isNoNamespacedName) {
            return null;
        }
        // make sure to compare FQNs
        $objectType = $this->expandShortenedObjectType($staticType);
        foreach ($this->oldToNewTypes as $oldToNewType) {
            if (!$objectType->equals($oldToNewType->getOldType())) {
                continue;
            }
            $newTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($oldToNewType->getNewType());
            $parentType = $node->getAttribute(PhpDocAttributeKey::PARENT);
            if ($parentType instanceof TypeNode) {
                // mirror attributes
                $newTypeNode->setAttribute(PhpDocAttributeKey::PARENT, $parentType);
            }
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
    private function resolveNamespacedName(IdentifierTypeNode $identifierTypeNode, PhpParserNode $phpParserNode, string $name) : string
    {
        if (\strncmp($name, '\\', \strlen('\\')) === 0) {
            return $name;
        }
        if (\strpos($name, '\\') !== \false) {
            return $name;
        }
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($identifierTypeNode, $phpParserNode);
        if (!$staticType instanceof ObjectType) {
            return $name;
        }
        if ($staticType instanceof ShortenedObjectType) {
            return $name;
        }
        $uses = $this->useImportsResolver->resolve();
        $originalNode = $phpParserNode->getAttribute(AttributeKey::ORIGINAL_NODE) ?? $phpParserNode;
        $scope = $originalNode->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            if (!$phpParserNode->hasAttribute(AttributeKey::ORIGINAL_NODE)) {
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
     * @param Use_[]|GroupUse[] $uses
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
