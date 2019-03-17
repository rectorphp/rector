<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\Class_;

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
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://doc.nette.org/en/2.4/components
 * ↓
 * @see https://symfony.com/doc/current/controller.html
 */
final class NetteControlToSymfonyControllerRector extends AbstractRector
{
    /**
     * @var string
     */
    private $netteControlClass;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var Expr|null
     */
    private $templateFileExpr;

    /**
     * @var Expr[]
     */
    private $templateVariables = [];

    public function __construct(
        ClassManipulator $classManipulator,
        CallableNodeTraverser $callableNodeTraverser,
        string $netteControlClass = 'Nette\Application\UI\Control'
    ) {
        $this->classManipulator = $classManipulator;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->netteControlClass = $netteControlClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate Nette Component to Symfony Controller', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function render()
    {
        $this->template->param = 'some value';
        $this->template->render(__DIR__ . '/poll.latte');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

class SomeController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
     public function some()
     {
         $this->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
     }
}
CODE_SAMPLE
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

        if (! $this->isType($node, $this->netteControlClass)) {
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

        $renderMethod = $this->classManipulator->getMethod($node, 'render');
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

        $thisRenderMethod = new MethodCall(new Variable('this'), 'render');

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

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (
            Node $node
        ): void {
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
