<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\OneToOne;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\OneToOneTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Core\Exception\ShouldNotHappenException;

final class OneToOnePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return [OneToOne::class];
    }

    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        /** @var OneToOne|null $oneToOne */
        $oneToOne = $this->nodeAnnotationReader->readPropertyAnnotation($node, $annotationClass);
        if ($oneToOne === null) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);
        $fullyQualifiedTargetEntity = $this->resolveFqnTargetEntity($oneToOne->targetEntity, $node);

        return OneToOneTagValueNode::createFromAnnotationAndOriginalContent(
            $oneToOne,
            $annotationContent,
            $fullyQualifiedTargetEntity
        );
    }
}
