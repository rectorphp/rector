<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\OneToOne;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\OneToOneTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Exception\ShouldNotHappenException;

final class OneToOnePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getClass(): string
    {
        return OneToOne::class;
    }

    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        /** @var OneToOne|null $oneToOne */
        $oneToOne = $this->nodeAnnotationReader->readPropertyAnnotation($node, $this->getClass());
        if ($oneToOne === null) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        return new OneToOneTagValueNode(
            $oneToOne->targetEntity,
            $oneToOne->mappedBy,
            $oneToOne->inversedBy,
            $oneToOne->cascade,
            $oneToOne->fetch,
            $oneToOne->orphanRemoval,
            $annotationContent,
            $this->resolveFqnTargetEntity($oneToOne->targetEntity, $node)
        );
    }
}
