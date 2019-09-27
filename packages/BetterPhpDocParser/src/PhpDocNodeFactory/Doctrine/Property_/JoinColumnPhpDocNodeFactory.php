<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\JoinColumn;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Exception\ShouldNotHappenException;

final class JoinColumnPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getName(): string
    {
        return JoinColumnTagValueNode::SHORT_NAME;
    }

    /**
     * @return JoinColumnTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        /** @var JoinColumn|null $joinColumn */
        $joinColumn = $this->nodeAnnotationReader->readPropertyAnnotation($node, JoinColumn::class);
        if ($joinColumn === null) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        return $this->createFromAnnotationAndAnnotationContent($joinColumn, $annotationContent);
    }

    public function createFromAnnotationAndAnnotationContent(
        JoinColumn $joinColumn,
        string $annotationContent,
        ?string $tag = null
    ): JoinColumnTagValueNode {
        return new JoinColumnTagValueNode(
            $joinColumn->name,
            $joinColumn->referencedColumnName,
            $joinColumn->unique,
            $joinColumn->nullable,
            $joinColumn->onDelete,
            $joinColumn->columnDefinition,
            $joinColumn->fieldName,
            $annotationContent,
            $tag
        );
    }
}
