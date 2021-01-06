<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\Embedded;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EmbeddedTagValueNode;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\Contract\PhpDocNodeFactoryInterface;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;

final class DoctrineEmbeddedPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements PhpDocNodeFactoryInterface
{
    public function isMatch(string $tag): bool
    {
        return $tag === EmbeddedTagValueNode::TAG_NAME;
    }

    /**
     * @return (PhpDocTagValueNode&AttributeAwareInterface)|null
     */
    public function create(SmartTokenIterator $tokenIterator, string $annotationClass): ?AttributeAwareInterface
    {
        $node = $this->currentNodeProvider->getNode();

        /** @var Embedded|null $annotation */
        $annotation = $this->nodeAnnotationReader->readAnnotation($node, $annotationClass);
        if ($annotation === null) {
            return null;
        }

        $items = $this->annotationItemsResolver->resolve($annotation);
        $fullyQualifiedClassName = $this->resolveFqnTargetEntity($annotation->class, $node);

        return new EmbeddedTagValueNode($items, $fullyQualifiedClassName);
    }
}
