<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\JMS;

use JMS\DiExtraBundle\Annotation\Inject;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\JMS\JMSInjectTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class JMSInjectPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    public function getName(): string
    {
        return JMSInjectTagValueNode::SHORT_NAME;
    }

    /**
     * @return JMSInjectTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            return null;
        }

        /** @var Inject|null $inject */
        $inject = $this->nodeAnnotationReader->readPropertyAnnotation($node, JMSInjectTagValueNode::CLASS_NAME);
        if ($inject === null) {
            return null;
        }

        if ($inject->value === null) {
            $serviceName = $this->nameResolver->getName($node);
        } else {
            $serviceName = $inject->value;
        }

        // needed for proper doc block formatting
        $this->resolveContentFromTokenIterator($tokenIterator);

        return new JMSInjectTagValueNode($serviceName, $inject->required, $inject->strict);
    }
}
