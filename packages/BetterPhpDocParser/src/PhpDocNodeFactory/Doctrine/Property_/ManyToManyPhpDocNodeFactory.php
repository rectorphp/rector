<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\ManyToMany;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToManyTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Exception\ShouldNotHappenException;

final class ManyToManyPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getName(): string
    {
        return ManyToManyTagValueNode::SHORT_NAME;
    }

    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        /** @var ManyToMany|null $manyToMany */
        $manyToMany = $this->nodeAnnotationReader->readPropertyAnnotation($node, ManyToMany::class);
        if ($manyToMany === null) {
            return null;
        }

        return new ManyToManyTagValueNode(
            $manyToMany->targetEntity,
            $manyToMany->mappedBy,
            $manyToMany->inversedBy,
            $manyToMany->cascade,
            $manyToMany->fetch,
            $manyToMany->orphanRemoval,
            $manyToMany->indexBy,
            $annotationContent,
            $this->resolveFqnTargetEntity($manyToMany->targetEntity, $node)
        );
    }
}
