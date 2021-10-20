<?php

declare (strict_types=1);
namespace Rector\Naming\ExpectedNameResolver;

use RectorPrefix20211020\Doctrine\Inflector\Inflector;
use RectorPrefix20211020\Nette\Utils\Strings;
final class InflectorSingularResolver
{
    /**
     * @var array<string, string>
     */
    private const SINGULAR_VERB = ['news' => 'new'];
    /**
     * @var string
     * @see https://regex101.com/r/lbQaGC/3
     */
    private const CAMELCASE_REGEX = '#(?<camelcase>([a-z]+|[A-Z]{1,}[a-z]+|_))#';
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
     * @var Inflector
     */
    private $inflector;
    public function __construct(\RectorPrefix20211020\Doctrine\Inflector\Inflector $inflector)
    {
        $this->inflector = $inflector;
    }
    public function resolve(string $currentName) : string
    {
        $matchBy = \RectorPrefix20211020\Nette\Utils\Strings::match($currentName, self::BY_MIDDLE_REGEX);
        if ($matchBy) {
            return \RectorPrefix20211020\Nette\Utils\Strings::substring($currentName, 0, -\strlen($matchBy['by']));
        }
        if (\array_key_exists($currentName, self::SINGULAR_VERB)) {
            return self::SINGULAR_VERB[$currentName];
        }
        if (\strpos($currentName, self::SINGLE) === 0) {
            return $currentName;
        }
        $camelCases = \RectorPrefix20211020\Nette\Utils\Strings::matchAll($currentName, self::CAMELCASE_REGEX);
        $singularValueVarName = '';
        foreach ($camelCases as $camelCase) {
            $singularValueVarName .= $this->inflector->singularize($camelCase['camelcase']);
        }
        if ($singularValueVarName === '' || $singularValueVarName === '_') {
            return $currentName;
        }
        $singularValueVarName = $singularValueVarName === $currentName ? self::SINGLE . \ucfirst($singularValueVarName) : $singularValueVarName;
        if (\strpos($singularValueVarName, self::SINGLE) !== 0) {
            return $singularValueVarName;
        }
        $length = \strlen($singularValueVarName);
        if ($length < 40) {
            return $singularValueVarName;
        }
        return $currentName;
    }
}
