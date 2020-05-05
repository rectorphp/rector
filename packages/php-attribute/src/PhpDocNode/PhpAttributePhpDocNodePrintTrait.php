<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\PhpDocNode;

trait PhpAttributePhpDocNodePrintTrait
{
    /**
     * @param mixed[] $items
     */
    public function printPhpAttributeItems(array $items): string
    {
        if ($items === []) {
            return '';
        }

        foreach ($items as $key => $item) {
            if (! is_array($item)) {
                continue;
            }

            $items[$key] = $this->printPhpAttributeItems($item);
        }

        return '(' . implode(', ', $items) . ')';
    }

    public function printItemsToAttributeAsArrayString(array $items): string
    {
        $items = $this->filterOutMissingItems($items);
        $items = $this->completeItemsQuotes($items);

        $content = $this->printPhpAttributeItemsAsArray($items);

        return $this->printPhpAttributeContent($content);
    }

    public function printItemsToAttributeString(array $items): string
    {
        $items = $this->filterOutMissingItems($items);
        $items = $this->completeItemsQuotes($items);

        $content = $this->printPhpAttributeItems($items);
        return $this->printPhpAttributeContent($content);
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
