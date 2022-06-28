<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory\Annotations;

use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
final class ValueQuoteWrapper
{
    /**
     * @return mixed|CurlyListNode|string
     * @param mixed $value
     */
    public function wrap($value)
    {
        if (\is_string($value)) {
            return '"' . $value . '"';
        }
        if (\is_array($value)) {
            return $this->wrapArray($value);
        }
        return $value;
    }
    /**
     * @param mixed[] $value
     */
    private function wrapArray(array $value) : CurlyListNode
    {
        $wrappedValue = [];
        foreach ($value as $nestedKey => $nestedValue) {
            switch (\true) {
                case \is_numeric($nestedKey):
                    $key = $nestedKey;
                    break;
                default:
                    $key = '"' . $nestedKey . '"';
                    break;
            }
            switch (\true) {
                case \is_string($nestedValue):
                    $wrappedValue[$key] = '"' . $nestedValue . '"';
                    break;
                default:
                    $wrappedValue[$key] = $nestedValue;
                    break;
            }
        }
        return new CurlyListNode($wrappedValue);
    }
}
