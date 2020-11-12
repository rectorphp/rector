<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

use Nette\Utils\Strings;

final class ContentPatcher
{
    /**
     * @see https://regex101.com/r/cLgjQf/3
     * @var string
     */
    public const VALID_ANNOTATION_STRING_REGEX = '#\*\s+@.*".{1,}"}\)#';

    /**
     * @see https://regex101.com/r/BhxeM8/3
     * @var string
     */
    public const INVALID_ANNOTATION_STRING_REGEX = '#\*\s+@.*.{1,}[^"]}\)#';

    /**
     * @see https://regex101.com/r/wpVS09/1
     * @var string
     */
    public const VALID_ANNOTATION_ROUTE_REGEX = '#\*\s+@.*:\s?".{1,}"}\)#';

    /**
     * @see https://regex101.com/r/cIgWGi/1
     * @var string
     */
    public const INVALID_ANNOTATION_ROUTE_REGEX = '#\*\s+@.*=\s?".{1,}"}\)#';

    /**
     * @see https://regex101.com/r/nCPUz9/2
     * @var string
     */
    public const VALID_ANNOTATION_COMMENT_REGEX = '#\*\s+@.*="[^"]*"}\)#';

    /**
     * @see https://regex101.com/r/xPg2yo/1
     * @var string
     */
    public const INVALID_ANNOTATION_COMMENT_REGEX = '#\*\s+@.*=".*"}\)#';

    /**
     * @see https://regex101.com/r/5HT5AW/7
     * @var string
     */
    public const VALID_ANNOTATION_CONSTRAINT_REGEX = '#\*\s+@.*\(?[\s\*]{0,}.*\s{0,}={(\s{0,}\*?\s{0,}".*",?){1,}[\s*]+}[\s\*]{1,}\)[\s\*}\)]{0,}#';

    /**
     * @see https://regex101.com/r/U8KzfW/7
     * @var string
     */
    public const INVALID_ANNOTATION_CONSTRAINT_REGEX = '#\*\s+@.*\(?[\s\*]{0,}.*\s{0,}={[^"].*(,[\s+\*]+.*)?}[\s\*]{1,}\)[\s\*}\)]{0,}#';

    /**
     * @see https://regex101.com/r/4mBd0y/2
     * @var string
     */
    private const CODE_MAY_DUPLICATE_REGEX = '#(if\s{0,}\(%s\(.*\{\s{0,}.*\s{0,}\}){2}#';

    /**
     * @see https://regex101.com/r/k48bUj/1
     * @var string
     */
    private const CODE_MAY_DUPLICATE_NO_BRACKET_REGEX = '#(if\s{0,}\(%s\(.*\s{1,}.*\s{0,}){2}#';

    /**
     * @see https://regex101.com/r/Ef83BV/1
     * @var string
     */
    private const SPACE_REGEX = '#\s#';

    /**
     * @see https://regex101.com/r/lC0i21/1
     * @var string
     */
    private const STAR_QUOTE_REGEX = '#[\*"]#';

    /**
     * @see https://regex101.com/r/j7agVx/1
     * @var string
     */
    private const ROUTE_VALID_REGEX = '#"\s?:\s?#';

    /**
     * @see https://regex101.com/r/qgp6Tr/1
     * @var string
     */
    private const ROUTE_INVALID_REGEX = '#"\s?=\s?#';

    /**
     * @var string[]
     */
    private const MAY_DUPLICATE_FUNC_CALLS = ['interface_exists', 'trait_exists'];

    /**
     * @see https://github.com/rectorphp/rector/issues/4499
     */
    public function cleanUpDuplicateContent(string $content): string
    {
        foreach (self::MAY_DUPLICATE_FUNC_CALLS as $mayDuplicateFuncCall) {
            $matches = Strings::match($content, sprintf(self::CODE_MAY_DUPLICATE_REGEX, $mayDuplicateFuncCall));

            if ($matches === null) {
                $matches = Strings::match(
                    $content,
                    sprintf(self::CODE_MAY_DUPLICATE_NO_BRACKET_REGEX, $mayDuplicateFuncCall)
                );
            }

            if ($matches === null) {
                continue;
            }

            $firstMatch = Strings::replace($matches[0], self::SPACE_REGEX, '');
            $secondMatch = Strings::replace($matches[1], self::SPACE_REGEX, '');

            if ($firstMatch === str_repeat($secondMatch, 2)) {
                $content = str_replace($matches[0], $matches[1], $content);
            }
        }

        return $content;
    }

    /**
     * @see https://github.com/rectorphp/rector/issues/3673
     * @see https://github.com/rectorphp/rector/issues/4274
     * @see https://github.com/rectorphp/rector/issues/4573
     * @see https://github.com/rectorphp/rector/issues/4581
     * @see https://github.com/rectorphp/rector/issues/4476
     */
    public function rollbackValidAnnotation(
        string $originalContent,
        string $content,
        string $validAnnotationRegex,
        string $invalidAnnotationRegex
    ): string {
        $matchesValidAnnotation = Strings::matchAll($originalContent, $validAnnotationRegex);
        if ($matchesValidAnnotation === []) {
            return $content;
        }

        $matchesInValidAnnotation = Strings::matchAll($content, $invalidAnnotationRegex);
        if ($matchesInValidAnnotation === []) {
            return $content;
        }

        if (count($matchesValidAnnotation) !== count($matchesInValidAnnotation)) {
            return $content;
        }

        foreach ($matchesValidAnnotation as $key => $match) {
            $validAnnotation = $match[0];
            $invalidAnnotation = $matchesInValidAnnotation[$key][0];

            if ($this->isSkipped($validAnnotationRegex, $validAnnotation, $invalidAnnotation)) {
                continue;
            }

            $content = str_replace($invalidAnnotation, $validAnnotation, $content);
        }

        return $content;
    }

    private function isSkipped(string $validAnnotationRegex, string $validAnnotation, string $invalidAnnotation): bool
    {
        $validAnnotation = Strings::replace($validAnnotation, self::SPACE_REGEX, '');
        $invalidAnnotation = Strings::replace($invalidAnnotation, self::SPACE_REGEX, '');

        if ($validAnnotationRegex !== self::VALID_ANNOTATION_ROUTE_REGEX) {
            $validAnnotation = Strings::replace($validAnnotation, self::STAR_QUOTE_REGEX, '');
            $invalidAnnotation = Strings::replace($invalidAnnotation, self::STAR_QUOTE_REGEX, '');

            return $validAnnotation !== $invalidAnnotation;
        }

        $validAnnotation = Strings::replace($validAnnotation, self::ROUTE_VALID_REGEX, '');
        $invalidAnnotation = Strings::replace($invalidAnnotation, self::ROUTE_INVALID_REGEX, '');

        return $validAnnotation !== $invalidAnnotation;
    }
}
