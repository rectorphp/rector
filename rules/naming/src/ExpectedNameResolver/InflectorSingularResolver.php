<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use Doctrine\Inflector\Inflector;
use Nette\Utils\Strings;

final class InflectorSingularResolver
{
    /**
     * @var array<string, string>
     */
    private const SINGULAR_VERB = [
        'news' => 'new',
    ];

    /**
     * @var string
     * @see https://regex101.com/r/lbQaGC/1
     */
    private const CAMELCASE_REGEX = '#(?<camelcase>([a-z]+|[A-Z]{1,}[a-z]+))#';

    /**
     * @var string
     * @see https://regex101.com/r/2aGdkZ/1
     */
    private const BY_MIDDLE_REGEX = '#(?<by>By[A-Z][a-z]+)#';

    /**
     * @var string
     */
    private const SINGLE = 'single';

    /**
     * @var Inflector
     */
    private $inflector;

    public function __construct(Inflector $inflector)
    {
        $this->inflector = $inflector;
    }

    public function resolve(string $currentName): string
    {
        $matchBy = Strings::match($currentName, self::BY_MIDDLE_REGEX);
        if ($matchBy) {
            $newName = Strings::substring($currentName, 0, - strlen($matchBy['by']));
            return $this->resolve($newName);
        }

        if (array_key_exists($currentName, self::SINGULAR_VERB)) {
            return self::SINGULAR_VERB[$currentName];
        }

        if (strpos($currentName, (string) self::SINGLE) === 0) {
            return $currentName;
        }

        $camelCases = Strings::matchAll($currentName, self::CAMELCASE_REGEX);
        $singularValueVarName = '';
        foreach ($camelCases as $camelCase) {
            $singularValueVarName .= $this->inflector->singularize($camelCase['camelcase']);
        }

        $singularValueVarName = $singularValueVarName === $currentName
            ? self::SINGLE . ucfirst($singularValueVarName)
            : $singularValueVarName;

        $length = strlen($singularValueVarName);
        if (strpos($singularValueVarName, (string) self::SINGLE) !== 0) {
            return $singularValueVarName;
        }
        if ($length < 40) {
            return $singularValueVarName;
        }
        return $currentName;
    }
}
