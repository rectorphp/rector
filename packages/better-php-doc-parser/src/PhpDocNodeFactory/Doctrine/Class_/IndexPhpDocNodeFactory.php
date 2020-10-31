<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Class_;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\Annotation\AnnotationItemsResolver;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\IndexTagValueNode;

final class IndexPhpDocNodeFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/wkjfUt/1
     */
    private const INDEX_REGEX = '#(?<tag>@(ORM\\\\)?Index)\((?<content>.*?)\),?#si';

    /**
     * @var AnnotationItemsResolver
     */
    private $annotationItemsResolver;

    public function __construct(AnnotationItemsResolver $annotationItemsResolver)
    {
        $this->annotationItemsResolver = $annotationItemsResolver;
    }

    /**
     * @param mixed[]|null $indexes
     * @return IndexTagValueNode[]
     */
    public function createIndexTagValueNodes(?array $indexes, string $annotationContent): array
    {
        if ($indexes === null) {
            return [];
        }

        $indexContents = Strings::matchAll($annotationContent, self::INDEX_REGEX);

        $indexTagValueNodes = [];

        foreach ($indexes as $key => $index) {
            $currentContent = $indexContents[$key];
            $items = $this->annotationItemsResolver->resolve($index);
            $indexTagValueNodes[] = new IndexTagValueNode($items, $currentContent['content'], $currentContent['tag']);
        }

        return $indexTagValueNodes;
    }
}
