<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\JMS;

use JMS\DiExtraBundle\Annotation\Inject;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\SpecificPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectTagValueNode;
use Rector\NodeNameResolver\NodeNameResolver;

final class JMSInjectPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements SpecificPhpDocNodeFactoryInterface
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return ['JMS\DiExtraBundle\Annotation\Inject'];
    }

    /**
     * @return JMSInjectTagValueNode|null
     */
    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        if (! $node instanceof Property) {
            return null;
        }

        /** @var Inject|null $inject */
        $inject = $this->nodeAnnotationReader->readPropertyAnnotation($node, $annotationClass);
        if ($inject === null) {
            return null;
        }

        $serviceName = $inject->value === null ? $this->nodeNameResolver->getName($node) : $inject->value;

        // needed for proper doc block formatting
        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        $items = $this->annotationItemsResolver->resolve($inject);
        return new JMSInjectTagValueNode($items, $serviceName, $annotationContent);
    }
}
