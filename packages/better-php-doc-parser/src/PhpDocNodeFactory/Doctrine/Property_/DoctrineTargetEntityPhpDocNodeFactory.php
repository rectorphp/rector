<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToOneTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\Contract\PhpDocNodeFactoryInterface;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;
use Rector\PhpdocParserPrinter\ValueObject\Tag;

final class DoctrineTargetEntityPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements PhpDocNodeFactoryInterface
{
    /**
     * @var array<string, string>
     */
    private const ANNOTATION_TO_NODE = [
        'Doctrine\ORM\Mapping\OneToOne' => OneToOneTagValueNode::class,
        'Doctrine\ORM\Mapping\OneToMany' => OneToManyTagValueNode::class,
        'Doctrine\ORM\Mapping\ManyToMany' => ManyToManyTagValueNode::class,
        'Doctrine\ORM\Mapping\ManyToOne' => ManyToOneTagValueNode::class,
    ];

    /**
     * @return (PhpDocTagValueNode&AttributeAwareInterface)|null
     */
    public function create(SmartTokenIterator $smartTokenIterator, Tag $tag): ?AttributeAwareInterface
    {
        $currentNode = $this->currentNodeProvider->getNode();
        if ($currentNode === null) {
            throw new ShouldNotHappenException();
        }

        $fullyQualifiedClass = $tag->getFullyQualifiedClass();
        if ($fullyQualifiedClass === null) {
            throw new ShouldNotHappenException();
        }

        /** @var OneToOne|OneToMany|ManyToMany|ManyToOne|null $annotation */
        $annotation = $this->nodeAnnotationReader->readAnnotation($currentNode, $fullyQualifiedClass);
        if ($annotation === null) {
            return null;
        }

        $tagValueNodeClass = self::ANNOTATION_TO_NODE[$tag->getFullyQualifiedClass()];

        $items = $this->annotationItemsResolver->resolve($annotation);
        $fullyQualifiedTargetEntity = $this->resolveFqnTargetEntity($annotation->targetEntity, $currentNode);

        return new $tagValueNodeClass($items, $fullyQualifiedTargetEntity);
    }

    public function isMatch(Tag $tag): bool
    {
        return in_array($tag->getFullyQualifiedClass(), self::ANNOTATION_TO_NODE, true);
    }
}
