<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use Doctrine\Inflector\Inflector;
use Nette\Utils\Strings;

/**
 * @see \Rector\Core\Tests\Naming\ExpectedNameResolver\InflectorSingularResolverTest
 */
final class InflectorSingularResolver
{
    /**
     * @var array<string, string>
     */
    private const SINGULARIZE_MAP = [
        'news' => 'new',
    ];

    /**
     * @var string
     * @see https://regex101.com/r/lbQaGC/3
     */
    private const CAMELCASE_REGEX = '#(?<camelcase>([a-z\d]+|[A-Z\d]{1,}[a-z\d]+|_))#';

    /**
     * @var string
     * @see https://regex101.com/r/2aGdkZ/2
     */
    private const BY_MIDDLE_REGEX = '#(?<by>By[A-Z][a-zA-Z]+)#';

    /**
     * @var string
     */
    private const SINGLE = 'single';

    /**
     * @var string
     */
    private const CAMELCASE = 'camelcase';

    public function __construct(
        private Inflector $inflector
    ) {
    }

    public function resolve(string $currentName): string
    {
        $matchBy = Strings::match($currentName, self::BY_MIDDLE_REGEX);
        if ($matchBy) {
            return Strings::substring($currentName, 0, -strlen($matchBy['by']));
        }

        $resolvedValue = $this->resolveSingularizeMap($currentName);
        if ($resolvedValue !== null) {
            return $resolvedValue;
        }

        if (str_starts_with($currentName, self::SINGLE)) {
            return $currentName;
        }

        $singularValueVarName = $this->singularizeCamelParts($currentName);

        if (in_array($singularValueVarName, ['', '_'], true)) {
            return $currentName;
        }

        $singularValueVarName = $singularValueVarName === $currentName
            ? self::SINGLE . ucfirst($singularValueVarName)
            : $singularValueVarName;
        if (! str_starts_with($singularValueVarName, self::SINGLE)) {
            return $singularValueVarName;
        }

        $length = strlen($singularValueVarName);
        if ($length < 40) {
            return $singularValueVarName;
        }

        return $currentName;
    }

    private function resolveSingularizeMap(string $currentName): string|null
    {
        foreach (self::SINGULARIZE_MAP as $plural => $singular) {
            if ($currentName === $plural) {
                return $singular;
            }

            if (Strings::match($currentName, '#' . ucfirst($plural) . '#')) {
                $resolvedValue = Strings::replace($currentName, '#' . ucfirst($plural) . '#', ucfirst($singular));
                return $this->singularizeCamelParts($resolvedValue);
            }

            if (Strings::match($currentName, '#' . $plural . '#')) {
                $resolvedValue = Strings::replace($currentName, '#' . $plural . '#', $singular);
                return $this->singularizeCamelParts($resolvedValue);
            }
        }

        return null;
    }

    private function singularizeCamelParts(string $currentName): string
    {
        $camelCases = Strings::matchAll($currentName, self::CAMELCASE_REGEX);

        $resolvedName = '';
        foreach ($camelCases as $camelCase) {
            $value = $this->inflector->singularize($camelCase[self::CAMELCASE]);

            if (in_array($camelCase[self::CAMELCASE], ['is', 'has'], true)) {
                $value = $camelCase[self::CAMELCASE];
            }

            $resolvedName .= $value;
        }

        return $resolvedName;
    }
}
