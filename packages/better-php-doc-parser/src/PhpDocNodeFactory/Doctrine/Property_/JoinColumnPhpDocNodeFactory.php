<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\JoinColumn;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Core\Exception\ShouldNotHappenException;

final class JoinColumnPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return [JoinColumn::class];
    }

    /**
     * @return JoinColumnTagValueNode|null
     */
    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        /** @var JoinColumn|null $joinColumn */
        $joinColumn = $this->nodeAnnotationReader->readPropertyAnnotation($node, $annotationClass);
        if ($joinColumn === null) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        return JoinColumnTagValueNode::createFromAnnotationAndOriginalContent($joinColumn, $annotationContent);
    }
}
