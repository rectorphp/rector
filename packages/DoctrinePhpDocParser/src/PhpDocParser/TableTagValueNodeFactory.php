<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\PhpDocParser;

use Doctrine\ORM\Mapping\Annotation;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use Rector\DoctrinePhpDocParser\AnnotationReader\NodeAnnotationReader;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_\AbstractIndexTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_\IndexTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_\TableTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_\UniqueConstraintTagValueNode;
use Rector\Exception\ShouldNotHappenException;

final class TableTagValueNodeFactory
{
    /**
     * @var string
     */
    private const UNIQUE_CONSTRAINT_PATTERN = '#@ORM\\\\UniqueConstraint\((?<singleUniqueConstraint>.*?)\),?#s';

    /**
     * @var string
     */
    private const INDEX_PATTERN = '#@ORM\\\\Index\((?<singleIndex>.*?)\),?#s';

    /**
     * @var NodeAnnotationReader
     */
    private $nodeAnnotationReader;

    public function __construct(NodeAnnotationReader $nodeAnnotationReader)
    {
        $this->nodeAnnotationReader = $nodeAnnotationReader;
    }

    public function create(Class_ $class, string $annotationContent): TableTagValueNode
    {
        /** @var Table $table */
        $table = $this->nodeAnnotationReader->readClassAnnotation($class, Table::class);

        $indexTagValueNodes = $this->createIndexTagValueNodes($table->indexes, $annotationContent);
        $uniqueConstraintTagValueNodes = $this->createUniqueConstraintTagValueNodes(
            $table->uniqueConstraints,
            $annotationContent
        );

        return new TableTagValueNode(
            $table->name,
            $table->schema,
            $indexTagValueNodes,
            $uniqueConstraintTagValueNodes,
            $table->options,
            $annotationContent
        );
    }

    /**
     * @param mixed[]|null $indexes
     * @return IndexTagValueNode[]
     */
    private function createIndexTagValueNodes(?array $indexes, string $annotationContent): array
    {
        if ($indexes === null) {
            return [];
        }

        $indexContents = Strings::matchAll($annotationContent, self::INDEX_PATTERN);

        $indexTagValueNodes = [];
        foreach ($indexes as $key => $index) {
            $indexTagValueNodes[] = $this->createIndexOrUniqueConstantTagValueNode(
                $index,
                $indexContents[$key]['singleIndex']
            );
        }

        return $indexTagValueNodes;
    }

    /**
     * @return UniqueConstraintTagValueNode[]
     */
    private function createUniqueConstraintTagValueNodes(?array $uniqueConstraints, string $annotationContent): array
    {
        if ($uniqueConstraints === null) {
            return [];
        }

        $uniqueConstraintContents = Strings::matchAll($annotationContent, self::UNIQUE_CONSTRAINT_PATTERN);

        $uniqueConstraintTagValueNodes = [];
        foreach ($uniqueConstraints as $key => $uniqueConstraint) {
            $uniqueConstraintTagValueNodes[] = $this->createIndexOrUniqueConstantTagValueNode(
                $uniqueConstraint,
                $uniqueConstraintContents[$key]['singleUniqueConstraint']
            );
        }

        return $uniqueConstraintTagValueNodes;
    }

    /**
     * @param Index|UniqueConstraint $annotation
     * @param string $annotationContent
     * @return IndexTagValueNode|UniqueConstraintTagValueNode
     */
    private function createIndexOrUniqueConstantTagValueNode(
        Annotation $annotation,
        string $annotationContent
    ): AbstractIndexTagValueNode {
        // doctrine orm compatibility
        $flags = property_exists($annotation, 'flags') ? $annotation->flags : [];

        $arguments = [$annotation->name, $annotation->columns, $flags, $annotation->options, $annotationContent];

        if ($annotation instanceof UniqueConstraint) {
            return new UniqueConstraintTagValueNode(...$arguments);
        }

        if ($annotation instanceof Index) {
            return new IndexTagValueNode(...$arguments);
        }

        throw new ShouldNotHappenException();
    }
}
