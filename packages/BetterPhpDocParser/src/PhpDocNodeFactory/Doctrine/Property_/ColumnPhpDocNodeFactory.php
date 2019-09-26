<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\Column;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Exception\ShouldNotHappenException;

final class ColumnPhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
    public function getName(): string
    {
        return ColumnTagValueNode::SHORT_NAME;
    }

    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        /** @var Column|null $column */
        $column = $this->nodeAnnotationReader->readPropertyAnnotation($node, Column::class);
        if ($column === null) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        return new ColumnTagValueNode(
            $column->name,
            $column->type,
            $column->length,
            $column->precision,
            $column->scale,
            $column->unique,
            $column->nullable,
            $column->options,
            $column->columnDefinition,
            $annotationContent
        );
    }
}
