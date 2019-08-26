<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\PhpDocParser;

use Doctrine\ORM\Mapping\Annotation;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\Configuration\CurrentNodeProvider;
use Rector\DoctrinePhpDocParser\AnnotationReader\NodeAnnotationReader;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\ColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\EntityTagValueNode;
use Rector\Exception\NotImplementedException;

/**
 * Parses following ORM annotations:
 * - ORM\Entity
 */
final class OrmTagParser
{
    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var NodeAnnotationReader
     */
    private $nodeAnnotationReader;

    public function __construct(
        CurrentNodeProvider $currentNodeProvider,
        NodeAnnotationReader $nodeAnnotationReader
    ) {
        $this->currentNodeProvider = $currentNodeProvider;
        $this->nodeAnnotationReader = $nodeAnnotationReader;
    }

    public function parse(TokenIterator $tokenIterator, string $tag): PhpDocTagValueNode
    {
        /** @var Class_|Property $node */
        $node = $this->currentNodeProvider->getNode();

        // skip all tokens for this annotation, so next annotation can work with tokens after this one
        $annotationContent = $tokenIterator->joinUntil(
            Lexer::TOKEN_END,
            Lexer::TOKEN_PHPDOC_EOL,
            Lexer::TOKEN_CLOSE_PHPDOC
        );

        // Entity tags
        if ($node instanceof Class_) {
            if ($tag === '@ORM\Entity') {
                return $this->createEntityTagValueNode($node, $annotationContent);
            }
        }

        // Property tags
        if ($node instanceof Property) {
            if ($tag === '@ORM\Column') {
                return $this->createColumnTagValueNode($node, $annotationContent);
            }
        }

        // @todo
        throw new NotImplementedException(__METHOD__);
    }

    private function createEntityTagValueNode(Class_ $node, string $content): EntityTagValueNode
    {
        /** @var Entity $entity */
        $entity = $this->nodeAnnotationReader->readDoctrineClassAnnotation($node, Entity::class);

        $itemsOrder = $this->resolveAnnotationItemsOrder($content);

        return new EntityTagValueNode($entity->repositoryClass, $entity->readOnly, $itemsOrder);
    }

    private function createColumnTagValueNode(Property $property, string $content): ColumnTagValueNode
    {
        /** @var Column $column */
        $column = $this->nodeAnnotationReader->readDoctrinePropertyAnnotation($property, Column::class);

        $itemsOrder = $this->resolveAnnotationItemsOrder($content);

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
            $itemsOrder
        );
    }

    /**
     * @return string[]
     */
    private function resolveAnnotationItemsOrder(string $content): array
    {
        $itemsOrder = [];
        $matches = Strings::matchAll($content, '#(?<item>\w+)=#m');
        foreach ($matches as $match) {
            $itemsOrder[] = $match['item'];
        }

        return $itemsOrder;
    }
}
