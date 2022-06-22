<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory\Annotations;

use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
final class StringValueQuoteWrapper
{
    /**
     * @return mixed|CurlyListNode|string
     * @param mixed $value
     */
    public function wrap($value, ?string $key)
    {
        if (\is_string($value)) {
            return '"' . $value . '"';
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
                $value[$nestedKey] = '"' . $nestedValue . '"';
            }
        }
        return new CurlyListNode($value);
    }
}
