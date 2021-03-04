<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use Doctrine\Inflector\Inflector;
use Nette\Utils\Strings;
use PhpParser\Node;

final class InflectorSingularResolver
{
    /**
     * @var Inflector
     */
    private $inflector;

    /**
     * @var string
     * @see https://regex101.com/r/lbQaGC/1
     */
    private const CAMELCASE_REGEX = '#(?<camelcase>([a-z]+|[A-Z]{1,}[a-z]+))#';

    public function __construct(Inflector $inflector)
    {
        $this->inflector = $inflector;
    }

    public function resolve(string $currentName): string
    {
        if (strpos($currentName, 'single') === 0) {
            return $currentName;
        }

        $camelCases = Strings::matchAll($currentName, self::CAMELCASE_REGEX);
        $singularValueVarName = '';
        foreach ($camelCases as $camelCase) {
            $singularValueVarName .= $this->inflector->singularize($camelCase['camelcase']);
        }

        $singularValueVarName = $singularValueVarName === $currentName
            ? 'single' . ucfirst($singularValueVarName)
            : $singularValueVarName;

        $length = strlen($singularValueVarName);
        if ($length >= 40) {
            return $currentName;
        }

        return $singularValueVarName;
    }
}
