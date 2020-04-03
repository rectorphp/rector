<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Symfony\Validator\Constraints;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints\AssertTypeTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Symfony\Component\Validator\Constraints\Type;

final class AssertTypePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return Type::class;
    }

    /**
     * @return AssertTypeTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            return null;
        }

        /** @var Type|null $type */
        $type = $this->nodeAnnotationReader->readPropertyAnnotation($node, $this->getClass());
        if ($type === null) {
            return null;
        }

        // to skip tokens for this node
        $this->resolveContentFromTokenIterator($tokenIterator);

        return new AssertTypeTagValueNode($type->groups, $type->type);
    }
}
