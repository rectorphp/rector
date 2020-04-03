<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Gedmo;

use Gedmo\Mapping\Annotation\Slug;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Gedmo\SlugTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;

final class SlugPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return Slug::class;
    }

    /**
     * @return SlugTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            return null;
        }

        /** @var Slug|null $slug */
        $slug = $this->nodeAnnotationReader->readPropertyAnnotation($node, $this->getClass());
        if ($slug === null) {
            return null;
        }

        $annotationContent = $this->annotationContentResolver->resolveFromTokenIterator($tokenIterator);

        return new SlugTagValueNode(
            $slug->fields,
            $slug->updatable,
            $slug->style,
            $slug->unique,
            $slug->unique_base,
            $slug->separator,
            $slug->prefix,
            $slug->suffix,
            $slug->handlers,
            $slug->dateFormat,
            $annotationContent
        );
    }
}
