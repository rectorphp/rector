<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\NodeFactory;

use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Rector\Doctrine\CodeQuality\Helper\NodeValueNormalizer;
use RectorPrefix202402\Webmozart\Assert\Assert;
final class ArrayItemNodeFactory
{
    /**
     * @var string
     */
    private const QUOTE_ALL = '*';
    /**
     * These are handled in their own transformers
     *
     * @var string[]
     */
    private const EXTENSION_KEYS = ['gedmo', 'joinColumns'];
    /**
     * @param array<string, mixed> $propertyMapping
     * @return ArrayItemNode[]
     */
    public function createWithQuotes(array $propertyMapping) : array
    {
        return $this->create($propertyMapping, [self::QUOTE_ALL]);
    }
    /**
     * @param array<string, mixed> $propertyMapping
     * @param string[] $quotedFields
     *
     * @return ArrayItemNode[]
     */
    public function create(array $propertyMapping, array $quotedFields = []) : array
    {
        Assert::allString($quotedFields);
        $arrayItemNodes = [];
        foreach ($propertyMapping as $fieldKey => $fieldValue) {
            if (\in_array($fieldKey, self::EXTENSION_KEYS, \true)) {
                continue;
            }
            if (\is_array($fieldValue)) {
                $fieldValueArrayItemNodes = [];
                foreach ($fieldValue as $fieldSingleKey => $fieldSingleValue) {
                    $fieldSingleNode = NodeValueNormalizer::normalize($fieldSingleValue);
                    $fieldArrayItemNode = new ArrayItemNode($fieldSingleNode, \is_string($fieldSingleKey) ? new StringNode($fieldSingleKey) : null);
                    $fieldValueArrayItemNodes[] = $fieldArrayItemNode;
                }
                $arrayItemNodes[] = new ArrayItemNode(new CurlyListNode($fieldValueArrayItemNodes), $fieldKey);
                continue;
            }
            if ($quotedFields === [self::QUOTE_ALL]) {
                $arrayItemNodes[] = new ArrayItemNode(new StringNode($fieldValue), new StringNode($fieldKey));
                continue;
            }
            if (\in_array($fieldKey, [EntityMappingKey::NULLABLE, EntityMappingKey::COLUMN_PREFIX], \true) && \is_bool($fieldValue)) {
                $arrayItemNodes[] = new ArrayItemNode($fieldValue ? 'true' : 'false', $fieldKey);
                continue;
            }
            if (\in_array($fieldKey, $quotedFields, \true)) {
                $arrayItemNodes[] = new ArrayItemNode(new StringNode($fieldValue), $fieldKey);
                continue;
            }
            $fieldValue = NodeValueNormalizer::normalize($fieldValue);
            $arrayItemNodes[] = new ArrayItemNode($fieldValue, $fieldKey);
        }
        return $arrayItemNodes;
    }
}
