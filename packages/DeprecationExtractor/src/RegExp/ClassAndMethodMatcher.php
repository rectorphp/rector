<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\RegExp;

use Nette\Utils\Strings;

final class ClassAndMethodMatcher
{
    /**
     * Matches:
     * - SomeClass
     * - SomeNamespace\AnotherClass
     *
     * @var string
     */
    private const CLASS_PATTERN = '[A-Za-z\\\\]+';

    /**
     * Matches:
     * - isMethod()
     * - isMethod('arg')
     *
     * @var string
     */
    private const METHOD_PATTERN = '[A-Za-z]+\([A-Za-z\']*\)';

    /**
     * Same as @see self::METHOD_PATTERN
     *
     * Just ignores (arguments), so:
     * - isMethod('arg')
     * will match and return:
     * - isMethod()
     *
     * @var string
     */
    private const METHOD_WITHOUT_ARGUMENTS_PATTERN = '[A-Za-z]+';

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
        '::' .
        self::METHOD_PATTERN .
        ')#s';

    /**
     * @var string
     */
    private const CLASS_METHOD_WITHOUT_ARGUMENTS_PATTERN = '#^(?<classMethod>' .
        self::CLASS_PATTERN .
        '::' .
        self::METHOD_WITHOUT_ARGUMENTS_PATTERN .
        ')#s';

    public function matchClassWithMethod(string $content): string
    {
        $result = Strings::match($content, self::CLASS_METHOD_PATTERN);

        return $result['classMethod'] ?? '';
    }

    public function matchClassWithMethodWithoutArguments(string $content): string
    {
        $result = Strings::match($content, self::CLASS_METHOD_WITHOUT_ARGUMENTS_PATTERN);

        return $result['classMethod'] ?? '';
    }

    public function matchClassWithMethodInstead(string $content): string
    {
        $matches = Strings::match($content, self::CLASS_METHOD_INSTEAD_PATTERN);

        return $matches['classMethod'] ?? '';
    }

    public function matchLocalMethod(string $content): string
    {
        $matches = Strings::match($content, '#(?<method>' . self::METHOD_PATTERN . ')#');

        return $matches['classMethod'] ?? '';
    }

    /**
     * Only local namespaced class like:
     * - "use ContainerBuilder::getReflectionClass() instead"
     */
    public function matchNamespacedClassWithMethod(string $content): string
    {
        $matches = Strings::match($content, '#(?<classMethod>[A-Za-z]+::' . self::METHOD_PATTERN . ')#');

        return $matches['classMethod'] ?? '';
    }

    /**
     * @return mixed[]
     */
    public function matchMethodArguments(string $method): array
    {
        $matches = Strings::match($method, '#\((?<arguments>[^\)]*)\)#');
        if (! isset($matches['arguments']) || empty($matches['arguments'])) {
            return [];
        }

        $arguments = explode(', ', $matches['arguments']);

        return $this->normalizeMethodArguments($arguments);
    }

    private function isStringSurroundedBy(string $content, string $needle): bool
    {
        return Strings::startsWith($content, $needle) && Strings::endsWith($content, $needle);
    }

    /**
     * @param mixed[] $arguments
     * @return mixed[]
     */
    private function normalizeMethodArguments(array $arguments): array
    {
        foreach ($arguments as $key => $argument) {
            if ($this->isStringSurroundedBy($argument, '\'')) {
                $arguments[$key] = Strings::trim($argument, '\'');
            }
        }

        return $arguments;
    }
}
