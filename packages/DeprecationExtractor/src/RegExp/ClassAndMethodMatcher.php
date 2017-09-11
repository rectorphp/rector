<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\RegExp;

final class ClassAndMethodMatcher
{
    /**
     * @var string
     */
    private const CLASS_WITH_METHOD_PATTERN = '#^(?<classMethod>[A-Za-z]+[\\\\A-Za-z]+::[A-Za-z]+\([A-Za-z\']*\))#s';

    public function matchLocalMethod(string $content): ?string
    {
        dump($content);
        die;
    }

    public function matchClassWithMethod(string $content): ?string
    {
        dump($content);
        die;
    }
}
