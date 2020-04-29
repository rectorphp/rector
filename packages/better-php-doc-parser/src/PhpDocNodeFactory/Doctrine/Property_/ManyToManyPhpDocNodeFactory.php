<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\ManyToMany;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToManyTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Core\Exception\ShouldNotHappenException;

final class ManyToManyPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return ManyToMany::class;
    }

    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        /** @var ManyToMany|null $manyToMany */
        $manyToMany = $this->nodeAnnotationReader->readPropertyAnnotation($node, $this->getClass());
        if ($manyToMany === null) {
            return null;
        }

        $fullyQualifiedTargetEntity = $this->resolveFqnTargetEntity($manyToMany->targetEntity, $node);
        return ManyToManyTagValueNode::createFromAnnotationAndOriginalContent(
            $manyToMany,
            $annotationContent,
            $fullyQualifiedTargetEntity
        );
    }
}
