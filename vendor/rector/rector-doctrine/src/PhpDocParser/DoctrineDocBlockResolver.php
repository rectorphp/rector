<?php

declare (strict_types=1);
namespace Rector\Doctrine\PhpDocParser;

use RectorPrefix20210510\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\PhpDoc\ShortClassExpander;
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
    /**
     * @var ShortClassExpander
     */
    private $shortClassExpander;
    public function __construct(NodeRepository $nodeRepository, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider, ShortClassExpander $shortClassExpander)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeRepository = $nodeRepository;
        $this->reflectionProvider = $reflectionProvider;
        $this->shortClassExpander = $shortClassExpander;
    }
    /**
     * @param Class_|string|mixed $class
     */
    public function isDoctrineEntityClass($class) : bool
    {
        if ($class instanceof Class_) {
            return $this->isDoctrineEntityClassNode($class);
        }
        if (\is_string($class)) {
            return $this->isStringClassEntity($class);
        }
        throw new ShouldNotHappenException();
    }
    public function getTargetEntity(Property $property) : ?string
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClasses(['Doctrine\\ORM\\Mapping\\OneToMany', 'Doctrine\\ORM\\Mapping\\ManyToMany', 'Doctrine\\ORM\\Mapping\\OneToOne', 'Doctrine\\ORM\\Mapping\\ManyToOne']);
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $targetEntity = $doctrineAnnotationTagValueNode->getValue('targetEntity');
        return $this->shortClassExpander->resolveFqnTargetEntity($targetEntity, $property);
    }
    public function isInDoctrineEntityClass(Node $node) : bool
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (!$classLike instanceof Class_) {
            return \false;
        }
        return $this->isDoctrineEntityClass($classLike);
    }
    private function isDoctrineEntityClassNode(Class_ $class) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        return $phpDocInfo->hasByAnnotationClasses(['Doctrine\\ORM\\Mapping\\Entity', 'Doctrine\\ORM\\Mapping\\Embeddable']);
    }
    private function isStringClassEntity(string $class) : bool
    {
        if (!$this->reflectionProvider->hasClass($class)) {
            return \false;
        }
        $classNode = $this->nodeRepository->findClass($class);
        if ($classNode !== null) {
            return $this->isDoctrineEntityClass($classNode);
        }
        $classReflection = $this->reflectionProvider->getClass($class);
        $resolvedPhpDocBlock = $classReflection->getResolvedPhpDoc();
        if (!$resolvedPhpDocBlock instanceof ResolvedPhpDocBlock) {
            return \false;
        }
        // dummy check of 3rd party code without running it
        return (bool) Strings::match($resolvedPhpDocBlock->getPhpDocString(), self::ORM_ENTITY_EMBEDDABLE_SHORT_ANNOTATION_REGEX);
    }
}
