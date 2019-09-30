<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\Class_;

use Nette\Application\UI\Control;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://doc.nette.org/en/2.4/components
 * ↓
 * @see https://symfony.com/doc/current/controller.html
 * @see \Rector\NetteToSymfony\Tests\Rector\Class_\NetteControlToSymfonyControllerRector\NetteControlToSymfonyControllerRectorTest
 */
final class NetteControlToSymfonyControllerRector extends AbstractRector
{
    /**
     * @var Expr|null
     */
    private $templateFileExpr;

    /**
     * @var Expr[]
     */
    private $templateVariables = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate Nette Component to Symfony Controller', [
            new CodeSample(
                <<<'PHP'
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function render()
    {
        $this->template->param = 'some value';
        $this->template->render(__DIR__ . '/poll.latte');
    }
}
PHP
                ,
                <<<'PHP'
use Nette\Application\UI\Control;

class SomeController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
     public function some()
     {
         $this->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
     }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // anonymous class
        if ($node->name === null) {
            return null;
        }

        if (! $this->isObjectType($node, Control::class)) {
            return null;
        }

        $className = $node->name->toString();

        // @todo decouple to removeSuffix, removePrefix
        if (Strings::endsWith($className, 'Control')) {
            $className = Strings::substring($className, 0, -Strings::length('Control'));
        }
        $className .= 'Controller';
        $node->name = new Identifier($className);

        $node->extends = new FullyQualified('Symfony\Bundle\FrameworkBundle\Controller\AbstractController');

        $renderMethod = $node->getMethod('render');
        if ($renderMethod !== null) {
            $this->processRenderMethod($renderMethod);
        }

        return $node;
    }

    private function processRenderMethod(ClassMethod $classMethod): void
    {
        // rename method - @todo pick
        $classMethod->name = new Identifier('action');

        $this->collectTemplateFileNameAndVariables($classMethod);

        $thisRenderMethod = $this->createMethodCall('this', 'render');

        if ($this->templateFileExpr !== null) {
            $thisRenderMethod->args[0] = new Arg($this->templateFileExpr);
        }

        if ($this->templateVariables !== []) {
            $thisRenderMethod->args[1] = new Arg($this->createTemplateVariablesArray());
        }

        $classMethod->stmts[] = new Expression($thisRenderMethod);
    }

    private function collectTemplateFileNameAndVariables(ClassMethod $classMethod): void
    {
        $this->templateFileExpr = null;
        $this->templateVariables = [];

        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node): void {
            if ($node instanceof MethodCall && $this->isName($node, 'render')) {
                $this->templateFileExpr = $node->args[0]->value;
                $this->removeNode($node);
            }

            if ($node instanceof Assign && $node->var instanceof PropertyFetch) {
                if (! $this->isName($node->var->var, 'template')) {
                    return;
                }

                $variableName = $this->getName($node->var);
                $this->templateVariables[$variableName] = $node->expr;

                $this->removeNode($node);
            }
        });
    }

    private function createTemplateVariablesArray(): Array_
    {
        $array = new Array_();
        foreach ($this->templateVariables as $name => $node) {
            $array->items[] = new ArrayItem($node, new String_($name));
        }

        return $array;
    }
}
