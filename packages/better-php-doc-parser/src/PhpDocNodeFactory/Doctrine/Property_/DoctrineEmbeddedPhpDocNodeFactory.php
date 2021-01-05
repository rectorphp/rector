<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\Embedded;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\GenericPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EmbeddedTagValueNode;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;

final class DoctrineEmbeddedPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements GenericPhpDocNodeFactoryInterface
{
    /**
     * @return array<string, string>
     */
    public function getTagValueNodeClassesToAnnotationClasses(): array
    {
        return [
            EmbeddedTagValueNode::class => 'Doctrine\ORM\Mapping\Embedded',
        ];
    }

    /**
     * @return (PhpDocTagValueNode&AttributeAwareInterface)|null
     */
    public function create(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?AttributeAwareInterface {
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
