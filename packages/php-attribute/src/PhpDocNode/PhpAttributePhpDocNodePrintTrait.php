<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\PhpDocNode;

trait PhpAttributePhpDocNodePrintTrait
{
    /**
     * @param string[] $items
     */
    public function printPhpAttributeItems(array $items): string
    {
        if ($items === []) {
            return '';
        }

        return '(' . implode(', ', $items) . ')';
    }

    protected function printPhpAttributeContent(string $content = ''): string
    {
        $attributeStart = '<<' . ltrim($this->getShortName(), '@');
        return $attributeStart . $content . '>>';
    }

    /**
     * @param string[] $items
     */
    protected function printPhpAttributeItemsAsArray(array $items): string
    {
        if ($items === []) {
            return '';
        }

        foreach ($items as $key => $value) {
            $items[$key] = sprintf('"%s" => %s', $key, $value);
        }

        return '([' . implode(', ', $items) . '])';
    }
}
