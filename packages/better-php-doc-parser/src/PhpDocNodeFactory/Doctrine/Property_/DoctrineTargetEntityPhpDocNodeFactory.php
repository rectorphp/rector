<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Contract\GenericPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToOneTagValueNode;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;

/**
 * @todo split to respect one pattern
 */
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

    /**
     * @return PhpDocTagValueNode&AttributeAwareInterface|null
     */
    public function create(SmartTokenIterator $smartTokenIterator, string $resolvedTag): ?AttributeAwareInterface
    {
        $currentNode = $this->currentNodeProvider->getNode();

        /** @var OneToOne|OneToMany|ManyToMany|ManyToOne|null $annotation */
        $annotation = $this->nodeAnnotationReader->readAnnotation($currentNode, $resolvedTag);
        if ($annotation === null) {
            return null;
        }

        $tagValueNodeClassesToAnnotationClasses = $this->getTagValueNodeClassesToAnnotationClasses();
        $tagValueNodeClass = array_search($resolvedTag, $tagValueNodeClassesToAnnotationClasses, true);

        $items = $this->annotationItemsResolver->resolve($annotation);
        $fullyQualifiedTargetEntity = $this->resolveFqnTargetEntity($annotation->targetEntity, $currentNode);

        return new $tagValueNodeClass($items, $fullyQualifiedTargetEntity);
    }

    public function isMatch(string $tag): bool
    {
    }
}
