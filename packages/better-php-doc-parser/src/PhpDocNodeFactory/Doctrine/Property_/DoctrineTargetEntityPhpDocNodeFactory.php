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
use Rector\BetterPhpDocParser\Contract\GenericPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToOneTagValueNode;

final class DoctrineTargetEntityPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements GenericPhpDocNodeFactoryInterface
{
    /**
     * @return array<string, string>
     */
    public function getTagValueNodeClassesToAnnotationClasses(): array
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
        /** @var OneToOne|OneToMany|ManyToMany|ManyToOne|null $annotation */
        $annotation = $this->nodeAnnotationReader->readAnnotation($node, $annotationClass);
        if ($annotation === null) {
            return null;
        }

        $tagValueNodeClassesToAnnotationClasses = $this->getTagValueNodeClassesToAnnotationClasses();
        $tagValueNodeClass = array_search($annotationClass, $tagValueNodeClassesToAnnotationClasses, true);

        $content = $this->resolveContentFromTokenIterator($tokenIterator);
        $items = $this->annotationItemsResolver->resolve($annotation);
        $fullyQualifiedTargetEntity = $this->resolveFqnTargetEntity($annotation->targetEntity, $node);

        return new $tagValueNodeClass($items, $content, $fullyQualifiedTargetEntity);
    }
}
