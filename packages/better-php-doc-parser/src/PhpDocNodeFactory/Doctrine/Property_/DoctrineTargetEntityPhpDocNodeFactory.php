<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToManyTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\OneToOneTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;

final class DoctrineTargetEntityPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return [
            OneToOneTagValueNode::class => 'Doctrine\ORM\Mapping\OneToOne',
            OneToManyTagValueNode::class => 'Doctrine\ORM\Mapping\OneToMany',
            ManyToManyTagValueNode::class => 'Doctrine\ORM\Mapping\ManyToMany',
            ManyToOneTagValueNode::class => 'Doctrine\ORM\Mapping\ManyToOne',
        ];
    }

    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        /** @var \Doctrine\ORM\Mapping\OneToOne|\Doctrine\ORM\Mapping\OneToMany|\Doctrine\ORM\Mapping\ManyToMany|\Doctrine\ORM\Mapping\ManyToOne|null $annotation */
        $annotation = $this->nodeAnnotationReader->readAnnotation($node, $annotationClass);
        if ($annotation === null) {
            return null;
        }

        $tagValueNodeClassToAnnotationClass = $this->getClasses();
        $tagValueNodeClass = array_search($annotationClass, $tagValueNodeClassToAnnotationClass, true);

        $content = $this->resolveContentFromTokenIterator($tokenIterator);
        $items = $this->annotationItemsResolver->resolve($annotation);
        $fullyQualifiedTargetEntity = $this->resolveFqnTargetEntity($annotation->targetEntity, $node);

        return new $tagValueNodeClass($items, $content, $fullyQualifiedTargetEntity);
    }
}
