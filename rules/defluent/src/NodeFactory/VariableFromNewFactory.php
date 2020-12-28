<?php

declare(strict_types=1);

namespace Rector\Defluent\NodeFactory;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NetteKdyby\Naming\VariableNaming;

final class VariableFromNewFactory
{
    /**
     * @var VariableNaming
     */
    private $variableNaming;

    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }

    public function create(New_ $new): Variable
    {
        $variableName = $this->variableNaming->resolveFromNode($new);
        if ($variableName === null) {
            throw new ShouldNotHappenException();
        }

        return new Variable($variableName);
    }
}
