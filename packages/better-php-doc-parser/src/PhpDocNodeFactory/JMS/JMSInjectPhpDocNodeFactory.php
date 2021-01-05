<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\JMS;

use JMS\DiExtraBundle\Annotation\Inject;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Contract\SpecificPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectTagValueNode;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;

final class JMSInjectPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements SpecificPhpDocNodeFactoryInterface
{
    public const TAG_NAME = 'JMS\DiExtraBundle\Annotation\Inject';

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    public function __construct(NodeNameResolver $nodeNameResolver, CurrentNodeProvider $currentNodeProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->currentNodeProvider = $currentNodeProvider;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return [self::TAG_NAME];
    }

    /**
     * @return JMSInjectTagValueNode|null
     */
    public function create(SmartTokenIterator $tokenIterator, string $annotationClass): ?AttributeAwareInterface
    {
        $node = $this->currentNodeProvider->getNode();
        if (! $node instanceof Property) {
            return null;
        }

        /** @var Inject|null $inject */
        $inject = $this->nodeAnnotationReader->readPropertyAnnotation($node, $annotationClass);
        if ($inject === null) {
            return null;
        }

        $serviceName = $inject->value === null ? $this->nodeNameResolver->getName($node) : $inject->value;

        $items = $this->annotationItemsResolver->resolve($inject);
        return new JMSInjectTagValueNode($items, $serviceName);
    }

    public function isMatch(string $tag): bool
    {
    }
}
