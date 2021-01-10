<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\Embedded;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EmbeddedTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\Contract\PhpDocNodeFactoryInterface;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;
use Rector\PhpdocParserPrinter\ValueObject\Tag;

final class DoctrineEmbeddedPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements PhpDocNodeFactoryInterface
{
    public function isMatch(Tag $tag): bool
    {
        return $tag->isMatch(EmbeddedTagValueNode::TAG_NAME);
    }

    /**
     * @return (PhpDocTagValueNode&AttributeAwareInterface)|null
     */
    public function create(SmartTokenIterator $tokenIterator, Tag $tag): ?AttributeAwareInterface
    {
        $currentNode = $this->currentNodeProvider->getNode();
        if ($currentNode === null) {
            throw new ShouldNotHappenException();
        }

        $fullyQualifiedClass = $tag->getFullyQualifiedClass();
        if ($fullyQualifiedClass === null) {
            throw new ShouldNotHappenException();
        }

        /** @var Embedded|null $annotation */
        $annotation = $this->nodeAnnotationReader->readAnnotation($currentNode, $fullyQualifiedClass);
        if ($annotation === null) {
            return null;
        }

        $items = $this->annotationItemsResolver->resolve($annotation);
        $fullyQualifiedClassName = $this->resolveFqnTargetEntity($annotation->class, $currentNode);

        return new EmbeddedTagValueNode($items, $fullyQualifiedClassName);
    }
}
