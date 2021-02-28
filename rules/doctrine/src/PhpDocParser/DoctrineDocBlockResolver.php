<?php

declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EmbeddableTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class DoctrineDocBlockResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/doLRPw/1
     */
    private const ORM_ENTITY_EMBEDDABLE_SHORT_ANNOTATION_REGEX = '#@ORM\\\\(Entity|Embeddable)#';

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(
        NodeRepository $nodeRepository,
        PhpDocInfoFactory $phpDocInfoFactory,
        ReflectionProvider $reflectionProvider
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeRepository = $nodeRepository;
        $this->reflectionProvider = $reflectionProvider;
    }

    /**
     * @param Class_|string|mixed $class
     */
    public function isDoctrineEntityClass($class): bool
    {
        if ($class instanceof Class_) {
            return $this->isDoctrineEntityClassNode($class);
        }

        if (is_string($class)) {
            return $this->isStringClassEntity($class);
        }

        throw new ShouldNotHappenException();
    }

    public function isDoctrineEntityClassWithIdProperty(Class_ $class): bool
    {
        if (! $this->isDoctrineEntityClass($class)) {
            return false;
        }

        foreach ($class->getProperties() as $property) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if ($phpDocInfo->hasByType(IdTagValueNode::class)) {
                return true;
            }
        }

        return false;
    }

    public function getTargetEntity(Property $property): ?string
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $doctrineRelationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
        if (! $doctrineRelationTagValueNode instanceof DoctrineRelationTagValueNodeInterface) {
            return null;
        }

        return $doctrineRelationTagValueNode->getTargetEntity();
    }

    public function isInDoctrineEntityClass(Node $node): bool
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        return $this->isDoctrineEntityClass($classLike);
    }

    private function isDoctrineEntityClassNode(Class_ $class): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        return $phpDocInfo->hasByTypes([EntityTagValueNode::class, EmbeddableTagValueNode::class]);
    }

    private function isStringClassEntity(string $class): bool
    {
        if (! $this->reflectionProvider->hasClass($class)) {
            return false;
        }

        $classNode = $this->nodeRepository->findClass($class);
        if ($classNode !== null) {
            return $this->isDoctrineEntityClass($classNode);
        }

        $classReflection = $this->reflectionProvider->getClass($class);
        $resolvedPhpDocBlock = $classReflection->getResolvedPhpDoc();
        if (! $resolvedPhpDocBlock instanceof ResolvedPhpDocBlock) {
            return false;
        }

        // dummy check of 3rd party code without running it
        return (bool) Strings::match(
            $resolvedPhpDocBlock->getPhpDocString(),
            self::ORM_ENTITY_EMBEDDABLE_SHORT_ANNOTATION_REGEX
        );
    }
}
