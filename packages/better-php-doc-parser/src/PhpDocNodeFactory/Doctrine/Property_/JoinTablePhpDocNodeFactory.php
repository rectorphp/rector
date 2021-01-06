<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\JoinTable;
use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Contract\SpecificPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinTableTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;

final class JoinTablePhpDocNodeFactory extends AbstractPhpDocNodeFactory implements SpecificPhpDocNodeFactoryInterface
{
    /**
     * @var string
     */
    private const INVERSE_JOIN_COLUMNS = 'inverseJoinColumns';

    /**
     * @var string
     */
    private const JOIN_COLUMNS = 'joinColumns';

    /**
     * @var string
     * @see https://regex101.com/r/5JVito/1
     */
    private const JOIN_COLUMN_REGEX = '#(?<tag>@(ORM\\\\)?JoinColumn)\((?<content>.*?)\),?#si';

    /**
     * @var string
     */
    private const TAG_NAME = 'Doctrine\ORM\Mapping\JoinTable';

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return [self::TAG_NAME];
    }

    /**
     * @return JoinTableTagValueNode|null
     */
    public function create(SmartTokenIterator $smartTokenIterator, string $annotationClass): ?AttributeAwareInterface
    {
        $node = $this->currentNodeProvider->getNode();
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        /** @var JoinTable|null $joinTable */
        $joinTable = $this->nodeAnnotationReader->readPropertyAnnotation($node, $annotationClass);
        if ($joinTable === null) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($smartTokenIterator);

        $joinColumnsAnnotationContent = $this->annotationContentResolver->resolveNestedKey(
            $annotationContent,
            self::JOIN_COLUMNS
        );

        $joinColumnValuesTags = $this->createJoinColumnTagValues(
            $joinColumnsAnnotationContent,
            $joinTable,
            self::JOIN_COLUMNS
        );

        // inversed join columns
        $inverseJoinColumnsAnnotationContent = $this->annotationContentResolver->resolveNestedKey(
            $annotationContent,
            self::INVERSE_JOIN_COLUMNS
        );
        $inverseJoinColumnValuesTags = $this->createJoinColumnTagValues(
            $inverseJoinColumnsAnnotationContent,
            $joinTable,
            self::INVERSE_JOIN_COLUMNS
        );

        return new JoinTableTagValueNode(
            $joinTable->name,
            $joinTable->schema,
            $joinColumnValuesTags,
            $inverseJoinColumnValuesTags
        );
    }

    public function isMatch(string $tag): bool
    {
        return $tag === self::TAG_NAME;
    }

    /**
     * @return JoinColumnTagValueNode[]
     */
    private function createJoinColumnTagValues(string $annotationContent, JoinTable $joinTable, string $type): array
    {
        $joinColumnContents = $this->matchJoinColumnContents($annotationContent);

        $joinColumnValuesTags = [];

        if (! in_array($type, [self::JOIN_COLUMNS, self::INVERSE_JOIN_COLUMNS], true)) {
            throw new ShouldNotHappenException();
        }

        $joinColumns = $type === self::JOIN_COLUMNS ? $joinTable->joinColumns : $joinTable->inverseJoinColumns;

        foreach ($joinColumns as $key => $joinColumn) {
            $subAnnotation = $joinColumnContents[$key];

            $items = $this->annotationItemsResolver->resolve($joinColumn);
            $joinColumnValuesTags[] = new JoinColumnTagValueNode($items, $subAnnotation['tag']);
        }

        return $joinColumnValuesTags;
    }

    /**
     * @return string[][]
     */
    private function matchJoinColumnContents(string $annotationContent): array
    {
        return Strings::matchAll($annotationContent, self::JOIN_COLUMN_REGEX);
    }
}
