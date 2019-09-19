<?php declare(strict_types=1);

namespace Rector\Doctrine\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineRelationTagValueNodeInterface;

trait DoctrineTrait
{
    /**
     * @var DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;

    /**
     * @required
     */
    public function autowireDoctrineTrait(DoctrineDocBlockResolver $doctrineDocBlockResolver): void
    {
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
    }

    protected function isDoctrineProperty(Property $property): bool
    {
        return $this->doctrineDocBlockResolver->isDoctrineProperty($property);
    }

    protected function isDoctrineEntityClass(Class_ $class): bool
    {
        return $this->doctrineDocBlockResolver->isDoctrineEntityClass($class);
    }

    protected function isInDoctrineEntityClass(Node $node): bool
    {
        return $this->doctrineDocBlockResolver->isInDoctrineEntityClass($node);
    }

    protected function getTargetEntity(Property $property): ?string
    {
        return $this->doctrineDocBlockResolver->getTargetEntity($property);
    }

    protected function getDoctrineRelationTagValueNode(Property $property): ?DoctrineRelationTagValueNodeInterface
    {
        return $this->doctrineDocBlockResolver->getDoctrineRelationTagValueNode($property);
    }
}
