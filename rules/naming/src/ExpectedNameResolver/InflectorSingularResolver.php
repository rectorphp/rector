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
        $singularValueVarName = $this->inflector->singularize($currentName);
        $singularValueVarName = $singularValueVarName === $currentName
            ? 'single' . ucfirst($singularValueVarName)
            : $singularValueVarName;

        return $singularValueVarName;
    }
}
