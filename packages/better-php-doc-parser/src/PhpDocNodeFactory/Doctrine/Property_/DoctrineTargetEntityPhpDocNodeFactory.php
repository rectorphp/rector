<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
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
            OneToOneTagValueNode::class => OneToOne::class,
            OneToManyTagValueNode::class => OneToMany::class,
            ManyToManyTagValueNode::class => ManyToMany::class,
            ManyToOneTagValueNode::class => ManyToOne::class,
        ];
    }

    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        /** @var OneToOne|OneToMany|ManyToMany|null $annotation */
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
