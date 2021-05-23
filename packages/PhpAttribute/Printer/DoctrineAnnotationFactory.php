<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Printer;

use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Scalar\String_;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class DoctrineAnnotationFactory
{
    public function __construct(
        private BetterStandardPrinter $betterStandardPrinter
    ) {
    }

    public function createFromAttribute(Attribute $attribute, string $className): DoctrineAnnotationTagValueNode
    {
        $items = $this->createItemsFromArgs($attribute->args);
        return new DoctrineAnnotationTagValueNode($className, null, $items);
    }

    /**
     * @param Arg[] $args
     * @return mixed[]
     */
    private function createItemsFromArgs(array $args): array
    {
        $items = [];
        foreach ($args as $arg) {
            if ($arg->value instanceof String_) {
                // standardize double quotes for annotations
                $arg->value->setAttribute(AttributeKey::KIND, String_::KIND_DOUBLE_QUOTED);
            }

            $itemValue = $this->betterStandardPrinter->print($arg->value);
            if ($arg->name !== null) {
                $name = $this->betterStandardPrinter->print($arg->name);
                $items[$name] = $itemValue;
            } else {
                $items[] = $itemValue;
            }
        }

        return $items;
    }
}
