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

    /**
     * @var string
     * @see https://regex101.com/r/uxtJDA/3
     */
    private const VARIABLE_CALL_OR_VARIABLE_REGEX = '#^\$([A-Za-z\-\>]+)[^\]](\(\))?#';

    /**
     * @var string
     * @see https://regex101.com/r/uxtJDA/1
     */
    private const STATIC_CALL_REGEX = '#([A-Za-z::\-\>]+)(\(\))$#';

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

    private function isKeywordToHighlight(string $word): bool
    {
        // already in code quotes
        if (Strings::startsWith($word, '`') || Strings::endsWith($word, '`')) {
            return false;
        }

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

        if ($word === 'composer.json') {
            return true;
        }

        if ((bool) Strings::match($word, self::VARIABLE_CALL_OR_VARIABLE_REGEX)) {
            return true;
        }

        return (bool) Strings::match($word, self::STATIC_CALL_REGEX);
    }
}
