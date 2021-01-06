<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Class_;

use Doctrine\ORM\Mapping\Table;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\Contract\SpecificPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\TableTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;

final class TablePhpDocNodeFactory extends AbstractPhpDocNodeFactory implements SpecificPhpDocNodeFactoryInterface
{
    /**
     * @var string
     */
    private const TAG_NAME = 'Doctrine\ORM\Mapping\Table';

    /**
     * @var IndexPhpDocNodeFactory
     */
    private $indexPhpDocNodeFactory;

    /**
     * @var UniqueConstraintPhpDocNodeFactory
     */
    private $uniqueConstraintPhpDocNodeFactory;

    public function __construct(
        IndexPhpDocNodeFactory $indexPhpDocNodeFactory,
        UniqueConstraintPhpDocNodeFactory $uniqueConstraintPhpDocNodeFactory
    ) {
        $this->indexPhpDocNodeFactory = $indexPhpDocNodeFactory;
        $this->uniqueConstraintPhpDocNodeFactory = $uniqueConstraintPhpDocNodeFactory;
    }

    public function isMatch(string $tag): bool
    {
        return $tag === self::TAG_NAME;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return [self::TAG_NAME];
    }

    public function create(SmartTokenIterator $smartTokenIterator, string $currentTag): ?AttributeAwareInterface
    {
        $currentNode = $this->currentNodeProvider->getNode();
        if (! $currentNode instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        /** @var Table|null $table */
        $table = $this->nodeAnnotationReader->readClassAnnotation($currentNode, $currentTag);
        if ($table === null) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($smartTokenIterator);

        $indexesContent = $this->annotationContentResolver->resolveNestedKey($annotationContent, 'indexes');
        $indexTagValueNodes = $this->indexPhpDocNodeFactory->createIndexTagValueNodes(
            $table->indexes,
            $indexesContent
        );

        $uniqueConstraintsContent = $this->annotationContentResolver->resolveNestedKey(
            $annotationContent,
            'uniqueConstraints'
        );

        $uniqueConstraintTagValueNodes = $this->uniqueConstraintPhpDocNodeFactory->createUniqueConstraintTagValueNodes(
            $table->uniqueConstraints,
            $uniqueConstraintsContent
        );

        return new TableTagValueNode(
            $table->name,
            $table->schema,
            $indexTagValueNodes,
            $uniqueConstraintTagValueNodes,
            $table->options
        );
    }
}
