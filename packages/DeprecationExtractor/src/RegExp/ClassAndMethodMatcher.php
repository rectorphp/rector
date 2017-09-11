<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\RegExp;

use Nette\Utils\Strings;

final class ClassAndMethodMatcher
{
    /**
     * @var string
     */
    private const CLASS_WITH_METHOD_PATTERN = '#^(?<classMethod>[A-Za-z]+[\\\\A-Za-z]+::[A-Za-z]+\([A-Za-z\']*\))#s';

    public function matchClassWithMethod(string $content): string
    {
        $result = Strings::match($content, self::CLASS_WITH_METHOD_PATTERN);

        return $result['classMethod'] ?? '';
    }
}
