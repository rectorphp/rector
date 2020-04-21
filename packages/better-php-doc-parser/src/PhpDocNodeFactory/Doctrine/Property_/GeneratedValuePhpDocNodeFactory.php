<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\GeneratedValue;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Core\Exception\ShouldNotHappenException;

final class GeneratedValuePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return GeneratedValue::class;
    }

    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        /** @var GeneratedValue|null $generatedValue */
        $generatedValue = $this->nodeAnnotationReader->readPropertyAnnotation($node, $this->getClass());
        if ($generatedValue === null) {
            return null;
        }

        // skip tokens for this annotation
        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        return new GeneratedValueTagValueNode($generatedValue->strategy, $annotationContent);
    }
}
