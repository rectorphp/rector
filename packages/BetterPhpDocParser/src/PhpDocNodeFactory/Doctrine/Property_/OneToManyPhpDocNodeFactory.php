<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\OneToMany;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\OneToManyTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Exception\ShouldNotHappenException;

final class OneToManyPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getName(): string
    {
        return OneToManyTagValueNode::SHORT_NAME;
    }

    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        /** @var OneToMany|null $oneToMany */
        $oneToMany = $this->nodeAnnotationReader->readPropertyAnnotation($node, OneToMany::class);
        if ($oneToMany === null) {
            return null;
        }

        return new OneToManyTagValueNode(
            $oneToMany->mappedBy,
            $oneToMany->targetEntity,
            $oneToMany->cascade,
            $oneToMany->fetch,
            $oneToMany->orphanRemoval,
            $oneToMany->indexBy,
            $annotationContent,
            $this->resolveFqnTargetEntity($oneToMany->targetEntity, $node)
        );
    }
}
