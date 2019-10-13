<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Sensio;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioMethodTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

final class SensioMethodPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return Method::class;
    }

    /**
     * @return SensioMethodTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        /** @var Method|null $method */
        $method = $this->nodeAnnotationReader->readMethodAnnotation($node, $this->getClass());
        if ($method === null) {
            return null;
        }

        // to skip tokens for this node
        $this->resolveContentFromTokenIterator($tokenIterator);

        return new SensioMethodTagValueNode($method->getMethods());
    }
}
