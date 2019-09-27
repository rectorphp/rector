<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\ManyToOne;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Exception\ShouldNotHappenException;

final class ManyToOnePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getName(): string
    {
        return ManyToOneTagValueNode::SHORT_NAME;
    }

    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        /** @var ManyToOne|null $manyToOne */
        $manyToOne = $this->nodeAnnotationReader->readPropertyAnnotation($node, ManyToOne::class);
        if ($manyToOne === null) {
            return null;
        }

        return new ManyToOneTagValueNode(
            $manyToOne->targetEntity,
            $manyToOne->cascade,
            $manyToOne->fetch,
            $manyToOne->inversedBy,
            $annotationContent,
            $this->resolveFqnTargetEntity($manyToOne->targetEntity, $node)
        );
    }
}
