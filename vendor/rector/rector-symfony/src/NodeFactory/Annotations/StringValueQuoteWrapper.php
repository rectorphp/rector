<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory\Annotations;

use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
final class StringValueQuoteWrapper
{
    /**
     * @readonly
     */
    private ArrayParser $arrayParser;
    public function __construct(ArrayParser $arrayParser)
    {
        $this->arrayParser = $arrayParser;
    }
    /**
     * @return mixed|CurlyListNode|StringNode
     * @param mixed $value
     */
    public function wrap($value, ?string $key)
    {
        if (\is_string($value)) {
            return new StringNode($value);
        }
        if (\is_array($value)) {
            return $this->wrapArray($value, $key);
        }
        return $value;
    }
    /**
     * @param mixed[] $value
     */
    private function wrapArray(array $value, ?string $key) : CurlyListNode
    {
        // include quotes in groups
        if (\in_array($key, ['groups', 'schemes', 'choices'], \true)) {
            foreach ($value as $nestedKey => $nestedValue) {
                if (\is_numeric($nestedValue)) {
                    continue;
                }
                $value[$nestedKey] = new StringNode($nestedValue);
            }
        }
        $arrayItemNodes = $this->arrayParser->createArrayFromValues($value);
        return new CurlyListNode($arrayItemNodes);
    }
}
