<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\Message;

use Nette\Utils\Strings;

final class ClassPrepender
{
    public function completeClassToLocalMethods(string $message, string $class): string
    {
        $completeMessage = '';
        $words = explode(' ', $message);

        foreach ($words as $word) {
            $completeMessage .= ' ' . $this->prependClassToMethodCallIfNeeded($word, $class);
        }

        return trim($completeMessage);
    }

    private function prependClassToMethodCallIfNeeded(string $word, string $class): string
    {
        // is method()
        if (Strings::endsWith($word, '()') && strlen($word) > 2) {
            // doesn't include class in the beggning
            if (! Strings::startsWith($word, $class)) {
                return $class . '::' . $word;
            }
        }

        // is method('...')
        if (Strings::endsWith($word, '\')')) {
            // doesn't include class in the beggning
            if (! Strings::startsWith($word, $class)) {
                return $class . '::' . $word;
            }
        }

        return $word;
    }
}
