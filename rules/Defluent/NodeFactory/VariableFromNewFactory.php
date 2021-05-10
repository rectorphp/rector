<?php

declare (strict_types=1);
namespace Rector\Defluent\NodeFactory;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Naming\Naming\VariableNaming;
final class VariableFromNewFactory
{
    /**
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function create(\PhpParser\Node\Expr\New_ $new) : \PhpParser\Node\Expr\Variable
    {
        $variableName = $this->variableNaming->resolveFromNode($new);
        if ($variableName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return new \PhpParser\Node\Expr\Variable($variableName);
    }
}
