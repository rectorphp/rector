<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\RegExp;

use Nette\Utils\Strings;

final class ClassAndMethodMatcher
{
    /**
     * @var string
     */
    private const CLASS_PATTERN = '[A-Za-z\\\\]+';

    /**
     * Matches:
     * - Use <class> instead
     * - Use the <class> instead
     * - Use the <class> class instead
     * - use the <class> class instead
     *
     * @var string
     */
    private const CLASS_METHOD_INSTEAD_PATTERN = '#use( the)? (?<classMethod>' .
        self::CLASS_PATTERN .
        ')( class)? instead#i';

    /**
     * @var string
     */
    private const CLASS_METHOD_PATTERN = '#^(?<classMethod>' .
        self::CLASS_PATTERN .
        '::[A-Za-z]+\([A-Za-z\']*\))#s';

    public function matchClassWithMethod(string $content): string
    {
        $result = Strings::match($content, self::CLASS_METHOD_PATTERN);

        return $result['classMethod'] ?? '';
    }

    public function matchClassWithMethodInstead(string $content): string
    {
        $matches = Strings::match($content, self::CLASS_METHOD_INSTEAD_PATTERN);

        return $matches['classMethod'] ?? '';
    }
}
