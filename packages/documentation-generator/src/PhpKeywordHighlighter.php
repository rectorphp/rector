<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator;

use Nette\Utils\Strings;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;

final class PhpKeywordHighlighter
{
    /**
     * @var string[]
     */
    private const TEXT_WORDS = [
        'Rename',
        'EventDispatcher',
        'current',
        'defined',
        'rename',
        'next',
        'file',
        'constant',
    ];

    public function highlight(string $content): string
    {
        $words = Strings::split($content, '# #');
        foreach ($words as $key => $word) {
            if (! $this->isKeywordToHighlight($word)) {
                continue;
            }

            $words[$key] = '`' . $word . '`';
        }

        return implode(' ', $words);
    }

    private function isKeywordToHighlight($word): bool
    {
        // part of normal text
        if (in_array($word, self::TEXT_WORDS, true)) {
            return false;
        }

        if (function_exists($word) || function_exists(trim($word, '()'))) {
            return true;
        }

        if (ClassExistenceStaticHelper::doesClassLikeExist($word)) {
            // not a class
            if (! Strings::contains($word, '\\')) {
                return in_array($word, ['Throwable', 'Exception'], true);
            }

            return true;
        }

        return $word === 'composer.json';
    }
}
