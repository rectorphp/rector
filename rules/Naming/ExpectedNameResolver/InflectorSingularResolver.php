<?php

declare (strict_types=1);
namespace Rector\Naming\ExpectedNameResolver;

use RectorPrefix20211029\Doctrine\Inflector\Inflector;
use RectorPrefix20211029\Nette\Utils\Strings;
/**
 * @see \Rector\Core\Tests\Naming\ExpectedNameResolver\InflectorSingularResolverTest
 */
final class InflectorSingularResolver
{
    /**
     * @var array<string, string>
     */
    private const SINGULARIZE_MAP = ['news' => 'new'];
    /**
     * @var string
     * @see https://regex101.com/r/lbQaGC/3
     */
    private const CAMELCASE_REGEX = '#(?<camelcase>([a-z\\d]+|[A-Z\\d]{1,}[a-z\\d]+|_))#';
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
     * @var \Doctrine\Inflector\Inflector
     */
    private $inflector;
    public function __construct(\RectorPrefix20211029\Doctrine\Inflector\Inflector $inflector)
    {
        $this->inflector = $inflector;
    }
    public function resolve(string $currentName) : string
    {
        $matchBy = \RectorPrefix20211029\Nette\Utils\Strings::match($currentName, self::BY_MIDDLE_REGEX);
        if ($matchBy) {
            return \RectorPrefix20211029\Nette\Utils\Strings::substring($currentName, 0, -\strlen($matchBy['by']));
        }
        $resolvedValue = $this->resolveSingularizeMap($currentName);
        if ($resolvedValue !== null) {
            return $resolvedValue;
        }
        if (\strncmp($currentName, self::SINGLE, \strlen(self::SINGLE)) === 0) {
            return $currentName;
        }
        $camelCases = \RectorPrefix20211029\Nette\Utils\Strings::matchAll($currentName, self::CAMELCASE_REGEX);
        $singularValueVarName = '';
        foreach ($camelCases as $camelCase) {
            $singularValueVarName .= $this->inflector->singularize($camelCase['camelcase']);
        }
        if (\in_array($singularValueVarName, ['', '_'], \true)) {
            return $currentName;
        }
        $singularValueVarName = $singularValueVarName === $currentName ? self::SINGLE . \ucfirst($singularValueVarName) : $singularValueVarName;
        if (\strncmp($singularValueVarName, self::SINGLE, \strlen(self::SINGLE)) !== 0) {
            return $singularValueVarName;
        }
        $length = \strlen($singularValueVarName);
        if ($length < 40) {
            return $singularValueVarName;
        }
        return $currentName;
    }
    /**
     * @return string|null
     */
    private function resolveSingularizeMap(string $currentName)
    {
        foreach (self::SINGULARIZE_MAP as $plural => $singular) {
            if ($currentName === $plural) {
                return $singular;
            }
            if (\RectorPrefix20211029\Nette\Utils\Strings::match($currentName, '#' . \ucfirst($plural) . '#')) {
                return \RectorPrefix20211029\Nette\Utils\Strings::replace($currentName, '#' . \ucfirst($plural) . '#', \ucfirst($singular));
            }
            if (\RectorPrefix20211029\Nette\Utils\Strings::match($currentName, '#' . $plural . '#')) {
                return \RectorPrefix20211029\Nette\Utils\Strings::replace($currentName, '#' . $plural . '#', $singular);
            }
        }
        return null;
    }
}
