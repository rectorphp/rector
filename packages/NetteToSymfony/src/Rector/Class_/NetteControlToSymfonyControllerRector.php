<?php

declare(strict_types=1);

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
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\ValueObject\PhpVersionFeature;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SomeController extends AbstractController
{
     public function some(): Response
     {
         return $this->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
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

        // skip presenter
        if ($this->isName($node->name, '*Presenter')) {
            return null;
        }

        if (! $this->isObjectType($node, Control::class)) {
            return null;
        }

        $className = $node->name->toString();

        $className = $this->removeSuffix($className, 'Control');
        $className .= 'Controller';

        $node->name = new Identifier($className);
        $node->extends = new FullyQualified(AbstractController::class);

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

        $this->collectTemplateFileNameAndVariablesAndRemoveDeadCode($classMethod);

        $thisRenderMethod = $this->createMethodCall('this', 'render');

        if ($this->templateFileExpr !== null) {
            $thisRenderMethod->args[0] = new Arg($this->templateFileExpr);
        }

        if ($this->templateVariables !== []) {
            $thisRenderMethod->args[1] = new Arg($this->createTemplateVariablesArray());
        }

        // add return in the end
        $return = new Return_($thisRenderMethod);
        $classMethod->stmts[] = $return;

        if ($this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            $classMethod->returnType = new FullyQualified(Response::class);
        }
    }

    private function collectTemplateFileNameAndVariablesAndRemoveDeadCode(ClassMethod $classMethod): void
    {
        $this->templateFileExpr = null;
        $this->templateVariables = [];

        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node): void {
            if ($node instanceof MethodCall) {
                $this->collectTemplateFileExpr($node);
            }

            if ($node instanceof Assign) {
                $this->collectVariableFromAssign($node);
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

    /**
     * Looks for:
     * $this->template
     */
    private function isTemplatePropertyFetch(Expr $expr): bool
    {
        if (! $expr instanceof PropertyFetch) {
            return false;
        }

        if (! $expr->var instanceof Variable) {
            return false;
        }

        if (! $this->isName($expr->var, 'this')) {
            return false;
        }

        return $this->isName($expr->name, 'template');
    }

    private function collectTemplateFileExpr(MethodCall $methodCall): void
    {
        if ($this->isName($methodCall->name, 'render')) {
            if (isset($methodCall->args[0])) {
                $this->templateFileExpr = $methodCall->args[0]->value;
            }

            $this->removeNode($methodCall);
        }

        if ($this->isName($methodCall->name, 'setFile')) {
            $this->templateFileExpr = $methodCall->args[0]->value;
            $this->removeNode($methodCall);
        }
    }

    private function collectVariableFromAssign(Assign $assign): void
    {
        // $this->template = x
        if ($assign->var instanceof PropertyFetch) {
            if (! $this->isName($assign->var->var, 'template')) {
                return;
            }

            $variableName = $this->getName($assign->var);
            $this->templateVariables[$variableName] = $assign->expr;

            $this->removeNode($assign);
        }

        // $x = $this->template
        if ($assign->var instanceof Variable) {
            if ($this->isTemplatePropertyFetch($assign->expr)) {
                $this->removeNode($assign);
            }
        }
    }

    private function removeSuffix(string $content, string $suffix): string
    {
        if (! Strings::endsWith($content, $suffix)) {
            return $content;
        }

        return Strings::substring($content, 0, -Strings::length($suffix));
    }
}
