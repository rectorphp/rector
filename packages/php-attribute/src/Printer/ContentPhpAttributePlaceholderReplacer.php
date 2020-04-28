<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Printer;

use Nette\Utils\Strings;
use Rector\PhpAttribute\Collector\PlaceholderToValueCollector;

final class ContentPhpAttributePlaceholderReplacer
{
    /**
     * @var PlaceholderToValueCollector
     */
    private $placeholderToValueCollector;

    public function __construct(PlaceholderToValueCollector $placeholderToValueCollector)
    {
        $this->placeholderToValueCollector = $placeholderToValueCollector;
    }

    public function decorateContent(string $content): string
    {
        // nothing to replace
        if ($this->placeholderToValueCollector->getMap() === []) {
            return $content;
        }

        $placeholderToValue = $this->placeholderToValueCollector->getMap();

        foreach ($placeholderToValue as $placeholder => $value) {
            $quotedPlaceholder = preg_quote($placeholder, '#');
            if (! Strings::match($content, '#' . $quotedPlaceholder . '#')) {
                continue;
            }

            $value = $this->addLeftIndent($content, $quotedPlaceholder, $value);
            $content = Strings::replace($content, '#' . $quotedPlaceholder . '#', $value);
        }

        // remove extra newline between attributes and node
        return Strings::replace($content, '#>>\n\n#', '>>' . PHP_EOL);
    }

    private function addLeftIndent(string $content, string $quotedPlaceholder, string $value): string
    {
        $matches = Strings::match($content, '#^(?<indent>\s+)' . $quotedPlaceholder . '#ms');
        if (! isset($matches['indent'])) {
            return $value;
        }

        $values = explode(PHP_EOL, $value);
        return implode(PHP_EOL . $matches['indent'], $values);
    }
}
