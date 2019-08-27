<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\PhpDocParser;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\Configuration\CurrentNodeProvider;
use Rector\DoctrinePhpDocParser\AnnotationReader\NodeAnnotationReader;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_\EntityTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\ColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\JoinColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\JoinTableTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\ManyToManyTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\ManyToOneTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\OneToManyTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\OneToOneTagValueNode;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineTagNodeInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

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

    public function parse(TokenIterator $tokenIterator, string $tag): ?PhpDocTagValueNode
    {
        /** @var Class_|Property $node */
        $node = $this->currentNodeProvider->getNode();

        $clonedTokenIterator = clone $tokenIterator;

        $singleLineContent = $clonedTokenIterator->joinUntil(
            Lexer::TOKEN_END,
            Lexer::TOKEN_PHPDOC_EOL,
            Lexer::TOKEN_CLOSE_PHPDOC
        );

        if ($singleLineContent === '' || Strings::match($singleLineContent, '#^\((.*?)\)$#m')) {
            $annotationContent = $singleLineContent;
            $tokenIterator->joinUntil(Lexer::TOKEN_END, Lexer::TOKEN_PHPDOC_EOL, Lexer::TOKEN_CLOSE_PHPDOC);
        } else { // multiline - content
            // skip all tokens for this annotation, so next annotation can work with tokens after this one
            $annotationContent = $tokenIterator->joinUntil(Lexer::TOKEN_END, Lexer::TOKEN_CLOSE_PHPDOC);
        }

        $annotationContent = $this->cleanMultilineAnnotationContent($annotationContent);

        // Entity tags
        if ($node instanceof Class_) {
            if ($tag === '@ORM\Entity') {
                return $this->createEntityTagValueNode($node, $annotationContent);
            }
        }

        // Property tags
        if ($node instanceof Property) {
            return $this->createPropertyTagValueNode($tag, $node, $annotationContent);
        }

        return null;
    }

    /**
     * @return ColumnTagValueNode|JoinColumnTagValueNode|OneToManyTagValueNode|ManyToManyTagValueNode|OneToOneTagValueNode|ManyToOneTagValueNode|JoinTableTagValueNode
     */
    private function createPropertyTagValueNode(
        string $tag,
        Property $property,
        string $annotationContent
    ): ?DoctrineTagNodeInterface {
        if ($tag === '@ORM\Column') {
            return $this->createColumnTagValueNode($property, $annotationContent);
        }

        if ($tag === '@ORM\JoinColumn') {
            return $this->createJoinColumnTagValueNode($property, $annotationContent);
        }

        if ($tag === '@ORM\ManyToMany') {
            return $this->createManyToManyTagValueNode($property, $annotationContent);
        }

        if ($tag === '@ORM\ManyToOne') {
            return $this->createManyToOneTagValueNode($property, $annotationContent);
        }

        if ($tag === '@ORM\OneToOne') {
            return $this->createOneToOneTagValueNode($property, $annotationContent);
        }

        if ($tag === '@ORM\OneToMany') {
            return $this->createOneToManyTagValueNode($property, $annotationContent);
        }

        if ($tag === '@ORM\JoinTable') {
            return $this->createJoinTableTagValeNode($property, $annotationContent);
        }

        return null;
    }

    private function createEntityTagValueNode(Class_ $node, string $annotationContent): EntityTagValueNode
    {
        /** @var Entity $entity */
        $entity = $this->nodeAnnotationReader->readDoctrineClassAnnotation($node, Entity::class);

        return new EntityTagValueNode($entity->repositoryClass, $entity->readOnly, $this->resolveAnnotationItemsOrder(
            $annotationContent
        ));
    }

    private function createColumnTagValueNode(Property $property, string $annotationContent): ColumnTagValueNode
    {
        /** @var Column $column */
        $column = $this->nodeAnnotationReader->readDoctrinePropertyAnnotation($property, Column::class);

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
            $this->resolveAnnotationItemsOrder($annotationContent)
        );
    }

    private function createManyToManyTagValueNode(Property $property, string $annotationContent): ManyToManyTagValueNode
    {
        /** @var ManyToMany $manyToMany */
        $manyToMany = $this->nodeAnnotationReader->readDoctrinePropertyAnnotation($property, ManyToMany::class);

        return new ManyToManyTagValueNode(
            $manyToMany->targetEntity,
            $manyToMany->mappedBy,
            $manyToMany->inversedBy,
            $manyToMany->cascade,
            $manyToMany->fetch,
            $manyToMany->orphanRemoval,
            $manyToMany->indexBy,
            $this->resolveAnnotationItemsOrder($annotationContent),
            $this->resolveFqnTargetEntity($manyToMany->targetEntity, $property)
        );
    }

    private function createManyToOneTagValueNode(Property $property, string $annotationContent): ManyToOneTagValueNode
    {
        /** @var ManyToOne $manyToOne */
        $manyToOne = $this->nodeAnnotationReader->readDoctrinePropertyAnnotation($property, ManyToOne::class);

        return new ManyToOneTagValueNode(
            $manyToOne->targetEntity,
            $manyToOne->cascade,
            $manyToOne->fetch,
            $manyToOne->inversedBy,
            $this->resolveAnnotationItemsOrder($annotationContent),
            $this->resolveFqnTargetEntity($manyToOne->targetEntity, $property)
        );
    }

    private function createOneToOneTagValueNode(Property $property, string $annotationContent): OneToOneTagValueNode
    {
        /** @var OneToOne $oneToOne */
        $oneToOne = $this->nodeAnnotationReader->readDoctrinePropertyAnnotation($property, OneToOne::class);

        return new OneToOneTagValueNode(
            $oneToOne->targetEntity,
            $oneToOne->mappedBy,
            $oneToOne->inversedBy,
            $oneToOne->cascade,
            $oneToOne->fetch,
            $oneToOne->orphanRemoval,
            $this->resolveAnnotationItemsOrder($annotationContent),
            $this->resolveFqnTargetEntity($oneToOne->targetEntity, $property)
        );
    }

    private function createOneToManyTagValueNode(Property $property, string $annotationContent): OneToManyTagValueNode
    {
        /** @var OneToMany $oneToMany */
        $oneToMany = $this->nodeAnnotationReader->readDoctrinePropertyAnnotation($property, OneToMany::class);

        return new OneToManyTagValueNode(
            $oneToMany->mappedBy,
            $oneToMany->targetEntity,
            $oneToMany->cascade,
            $oneToMany->fetch,
            $oneToMany->orphanRemoval,
            $oneToMany->indexBy,
            $this->resolveAnnotationItemsOrder($annotationContent),
            $this->resolveFqnTargetEntity($oneToMany->targetEntity, $property)
        );
    }

    private function createJoinColumnTagValueNode(Property $property, string $annotationContent): JoinColumnTagValueNode
    {
        /** @var JoinColumn $joinColumn */
        $joinColumn = $this->nodeAnnotationReader->readDoctrinePropertyAnnotation($property, JoinColumn::class);

        return $this->createJoinColumnTagValueNodeFromJoinColumnAnnotation($joinColumn, $annotationContent);
    }

    private function createJoinTableTagValeNode(Property $property, string $annotationContent): JoinTableTagValueNode
    {
        /** @var JoinTable $joinTable */
        $joinTable = $this->nodeAnnotationReader->readDoctrinePropertyAnnotation($property, JoinTable::class);

        $joinColumnContents = Strings::matchAll(
            $annotationContent,
            '#joinColumns=\{(\@ORM\\\\JoinColumn\((?<singleJoinColumn>.*?)\))+\}#s'
        );

        $joinColumnValuesTags = [];
        foreach ($joinTable->joinColumns as $key => $joinColumn) {
            $currentJoinColumnContent = $joinColumnContents[$key]['singleJoinColumn'];

            $joinColumnValuesTags[] = $this->createJoinColumnTagValueNodeFromJoinColumnAnnotation(
                $joinColumn,
                $currentJoinColumnContent
            );
        }

        $inverseJoinColumnContents = Strings::matchAll(
            $annotationContent,
            '#inverseJoinColumns=\{(\@ORM\\\\JoinColumn\((?<singleJoinColumn>.*?)\))+\}#'
        );

        $inverseJoinColumnValuesTags = [];
        foreach ($joinTable->inverseJoinColumns as $key => $inverseJoinColumn) {
            $currentInverseJoinColumnContent = $inverseJoinColumnContents[$key]['singleJoinColumn'];
            $inverseJoinColumnValuesTags[] = $this->createJoinColumnTagValueNodeFromJoinColumnAnnotation(
                $inverseJoinColumn,
                $currentInverseJoinColumnContent
            );
        }

        return new JoinTableTagValueNode(
            $joinTable->name,
            $joinTable->schema,
            $joinColumnValuesTags,
            $inverseJoinColumnValuesTags,
            $this->resolveAnnotationItemsOrder($annotationContent)
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

    private function resolveFqnTargetEntity(string $targetEntity, Node $node): string
    {
        if (class_exists($targetEntity)) {
            return $targetEntity;
        }

        $namespacedTargetEntity = $node->getAttribute(AttributeKey::NAMESPACE_NAME) . '\\' . $targetEntity;
        if (class_exists($namespacedTargetEntity)) {
            return $namespacedTargetEntity;
        }

        // probably tested class
        return $targetEntity;
    }

    private function createJoinColumnTagValueNodeFromJoinColumnAnnotation(
        JoinColumn $joinColumn,
        string $annotationContent
    ): JoinColumnTagValueNode {
        return new JoinColumnTagValueNode(
            $joinColumn->name,
            $joinColumn->referencedColumnName,
            $joinColumn->unique,
            $joinColumn->nullable,
            $joinColumn->onDelete,
            $joinColumn->columnDefinition,
            $joinColumn->fieldName,
            $this->resolveAnnotationItemsOrder($annotationContent)
        );
    }

    private function cleanMultilineAnnotationContent(string $annotationContent): string
    {
        return Strings::replace($annotationContent, '#(\s+)\*(\s+)#m', '$1$3');
    }
}
