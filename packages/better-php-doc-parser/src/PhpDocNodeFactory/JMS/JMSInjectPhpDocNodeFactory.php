<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\JMS;

use JMS\DiExtraBundle\Annotation\Inject;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\Contract\PhpDocNodeFactoryInterface;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;
use Rector\PhpdocParserPrinter\ValueObject\Tag;

final class JMSInjectPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements PhpDocNodeFactoryInterface
{
    /**
     * @var string
     */
    private const TAG_NAME = 'JMS\DiExtraBundle\Annotation\Inject';

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return JMSInjectTagValueNode|null
     */
    public function create(SmartTokenIterator $tokenIterator, Tag $tag): ?AttributeAwareInterface
    {
        $node = $this->currentNodeProvider->getNode();
        if (! $node instanceof Property) {
            return null;
        }

        $fullyQualifiedClass = $tag->getFullyQualifiedClass();
        if ($fullyQualifiedClass === null) {
            throw new ShouldNotHappenException();
        }

        /** @var Inject|null $inject */
        $inject = $this->nodeAnnotationReader->readPropertyAnnotation($node, $fullyQualifiedClass);
        if ($inject === null) {
            return null;
        }

        $serviceName = $inject->value === null ? $this->nodeNameResolver->getName($node) : $inject->value;

        $items = $this->annotationItemsResolver->resolve($inject);
        return new JMSInjectTagValueNode($items, $serviceName);
    }

    public function isMatch(Tag $tag): bool
    {
        return $tag->isMatch(self::TAG_NAME);
    }
}
