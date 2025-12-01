<?php

declare (strict_types=1);
namespace Rector\PhpAttribute;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use RectorPrefix202512\Webmozart\Assert\Assert;
final class AttributeArrayNameInliner
{
    /**
     * @var class-string
     */
    private const OPEN_API_PROPERTY_ATTRIBUTE = 'OpenApi\Attributes\Property';
    /**
     * @param Array_|list<Arg> $array
     * @return list<Arg>
     */
    public function inlineArrayToArgs($array, ?string $attributeClass = null): array
    {
        if (is_array($array)) {
            return $this->inlineArray($array, $attributeClass);
        }
        return $this->inlineArrayNode($array);
    }
    /**
     * @return list<Arg>
     */
    private function inlineArrayNode(Array_ $array): array
    {
        $args = [];
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if ($arrayItem->key instanceof String_) {
                $string = $arrayItem->key;
                $argumentName = new Identifier($string->value);
                $args[] = new Arg($arrayItem->value, \false, \false, [], $argumentName);
            } else {
                $args[] = new Arg($arrayItem->value);
            }
        }
        return $args;
    }
    /**
     * @param list<Arg> $args
     * @return list<Arg>
     */
    private function inlineArray(array $args, ?string $attributeClass = null): array
    {
        Assert::allIsAOf($args, Arg::class);
        foreach ($args as $arg) {
            if ($attributeClass === self::OPEN_API_PROPERTY_ATTRIBUTE && $arg->name instanceof Identifier && $arg->name->toString() === 'example') {
                continue;
            }
            if ($arg->value instanceof String_ && is_numeric($arg->value->value)) {
                // use equal over identical on purpose to verify if it is an integer
                if ((float) $arg->value->value == (int) $arg->value->value) {
                    $arg->value = new Int_((int) $arg->value->value);
                } else {
                    $arg->value = new Float_((float) $arg->value->value);
                }
            }
        }
        return $args;
    }
}
