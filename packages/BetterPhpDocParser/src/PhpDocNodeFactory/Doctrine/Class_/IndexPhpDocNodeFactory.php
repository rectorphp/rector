<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Class_;

use Doctrine\ORM\Mapping\Index;
use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\IndexTagValueNode;

final class IndexPhpDocNodeFactory
{
    /**
     * @var string
     */
    private const INDEX_PATTERN = '#@ORM\\\\Index\((?<singleIndex>.*?)\),?#si';

    /**
     * @param mixed[]|null $indexes
     * @return IndexTagValueNode[]
     */
    public function createIndexTagValueNodes(?array $indexes, string $annotationContent): array
    {
        if ($indexes === null) {
            return [];
        }

        $indexContents = Strings::matchAll($annotationContent, self::INDEX_PATTERN);

        $indexTagValueNodes = [];
        foreach ($indexes as $key => $index) {
            $indexTagValueNodes[] = $this->createFromAnnotationAndContent(
                $index,
                $indexContents[$key]['singleIndex']
            );
        }

        return $indexTagValueNodes;
    }

    private function createFromAnnotationAndContent(Index $index, string $annotationContent): IndexTagValueNode
    {
        // doctrine/orm compatibility between different versions
        $flags = property_exists($index, 'flags') ? $index->flags : [];

        return new IndexTagValueNode($index->name, $index->columns, $flags, $index->options, $annotationContent);
    }
}
