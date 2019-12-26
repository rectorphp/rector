<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\Class_;

use Nette\Application\UI\Control;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Exception\ShouldNotHappenException;
use Rector\Nette\TemplatePropertyAssignCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
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
     * @var TemplatePropertyAssignCollector
     */
    private $templatePropertyAssignCollector;

    public function __construct(TemplatePropertyAssignCollector $templatePropertyAssignCollector)
    {
        $this->templatePropertyAssignCollector = $templatePropertyAssignCollector;
    }

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
        $this->processGetPresenterGetSessionMethodCall($classMethod);

        // rename method - @todo pick
        $classMethod->name = new Identifier('action');

        [$templateFileExpr, $templateVariables, $nodesToRemove] = $this->templatePropertyAssignCollector->collectTemplateFileNameVariablesAndNodesToRemove(
            $classMethod
        );

        $thisRenderMethod = $this->createMethodCall('this', 'render');

        if ($templateFileExpr !== null) {
            $thisRenderMethod->args[0] = new Arg($templateFileExpr);
        }

        if ($templateVariables !== []) {
            $thisRenderMethod->args[1] = new Arg($this->createTemplateVariablesArray($templateVariables));
        }

        // add return in the end
        $return = new Return_($thisRenderMethod);
        $classMethod->stmts[] = $return;

        if ($this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            $classMethod->returnType = new FullyQualified(Response::class);
        }

        $this->removeNodes($nodesToRemove);
    }

    private function createTemplateVariablesArray(array $templateVariables): Array_
    {
        $array = new Array_();

        foreach ($templateVariables as $name => $node) {
            $array->items[] = new ArrayItem($node, new String_($name));
        }

        return $array;
    }

    private function removeSuffix(string $content, string $suffix): string
    {
        if (! Strings::endsWith($content, $suffix)) {
            return $content;
        }

        return Strings::substring($content, 0, -Strings::length($suffix));
    }

    private function processGetPresenterGetSessionMethodCall(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isName($node->name, 'getSession')) {
                return null;
            }

            if (! $node->var instanceof MethodCall) {
                return null;
            }

            if (! $this->isName($node->var->name, 'getPresenter')) {
                return null;
            }

            $node->var = new PropertyFetch(new Variable('this'), 'session');

            $class = $node->getAttribute(AttributeKey::CLASS_NODE);
            if (! $class instanceof Class_) {
                throw new ShouldNotHappenException();
            }

            $this->addPropertyToClass($class, new FullyQualifiedObjectType('Nette\Http\Session'), 'session');

            return $node;
        });
    }
}
