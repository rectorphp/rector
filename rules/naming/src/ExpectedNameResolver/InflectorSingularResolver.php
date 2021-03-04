<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use Doctrine\Inflector\Inflector;
use PhpParser\Node;

final class InflectorSingularResolver
{
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
        if (strpos($currentName, 'single') === 0) {
            return $currentName;
        }

        $singularValueVarName = $this->inflector->singularize($currentName);
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
