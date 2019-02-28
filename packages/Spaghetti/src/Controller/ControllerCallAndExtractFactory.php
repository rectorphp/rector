<?php declare(strict_types=1);

namespace Rector\Spaghetti\Controller;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\Exception\ShouldNotHappenException;

final class ControllerCallAndExtractFactory
{
    /**
     * @return Node[]
     */
    public function create(Class_ $controllerClass): array
    {
        if ($controllerClass->name === null) {
            throw new ShouldNotHappenException();
        }

        $newController = new New_(new Name($controllerClass->name->toString()));
        $renderMethodCall = new MethodCall($newController, 'render');

        $nodes = [];

        $variables = new Variable('variables');

        $variablesAssign = new Assign($variables, $renderMethodCall);
        $nodes[] = new Expression($variablesAssign);

        // extract($variables)
        $extractVariables = new FuncCall(new Name('extract'));
        $extractVariables->args[] = new Arg($variables);

        $nodes[] = new Expression($extractVariables);

        return $nodes;
    }
}
