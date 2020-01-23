<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\CakePHPToSymfony\Rector\AbstractCakePHPRector;
use Rector\CakePHPToSymfony\Template\TemplateMethodCallManipulator;
use Rector\CakePHPToSymfony\TemplatePathResolver;
use Rector\CodeQuality\CompactConverter;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/2/en/controllers.html#rendering-a-specific-view
 * @see https://symfony.com/doc/current/controller.html#rendering-templates
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRenderToSymfonyRector\CakePHPControllerRenderToSymfonyRectorTest
 */
final class CakePHPControllerRenderToSymfonyRector extends AbstractCakePHPRector
{
    /**
     * @var TemplatePathResolver
     */
    private $templatePathResolver;

    /**
     * @var CompactConverter
     */
    private $compactConverter;

    /**
     * @var TemplateMethodCallManipulator
     */
    private $templateMethodCallManipulator;

    public function __construct(
        TemplatePathResolver $templatePathResolver,
        CompactConverter $compactConverter,
        TemplateMethodCallManipulator $templateMethodCallManipulator
    ) {
        $this->templatePathResolver = $templatePathResolver;
        $this->compactConverter = $compactConverter;
        $this->templateMethodCallManipulator = $templateMethodCallManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP 2.4 Controller render() to Symfony 5', [
            new CodeSample(
                <<<'PHP'
class RedirectController extends \AppController
{
    public function index()
    {
        $this->render('custom_file');
    }
}
PHP
,
                <<<'PHP'
class RedirectController extends \AppController
{
    public function index()
    {
        return $this->render('redirect/custom_file.twig');
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInCakePHPController($node)) {
            return null;
        }

        if ($this->hasRenderMethodCall($node)) {
            $this->templateMethodCallManipulator->refactorExistingRenderMethodCall($node);
            return null;
        }

        $this->completeImplicitRenderMethodCall($node);

        return $node;
    }

    /**
     * Creates "$this->render('...', [...])";
     */
    private function createThisRenderMethodCall(ClassMethod $classMethod): MethodCall
    {
        $thisVariable = new Variable('this');
        $thisRenderMethodCall = new MethodCall($thisVariable, 'render');

        $templateName = $this->templatePathResolver->resolveForClassMethod($classMethod);
        $thisRenderMethodCall->args[] = new Arg(new String_($templateName));

        $setValues = $this->collectAndRemoveSetMethodCallArgs((array) $classMethod->stmts);

        if ($setValues !== []) {
            $parametersArray = $this->createArrayFromSetValues($setValues);
            $thisRenderMethodCall->args[] = new Arg($parametersArray);
        }

        return $thisRenderMethodCall;
    }

    /**
     * @return Arg[][]
     */
    private function collectAndRemoveSetMethodCallArgs(array $stmts): array
    {
        $setMethodCallArgs = [];

        $this->traverseNodesWithCallable($stmts, function (Node $node) use (&$setMethodCallArgs) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isName($node->name, 'set')) {
                return null;
            }

            $setMethodCallArgs[] = $node->args;
            $this->removeNode($node);

            return null;
        });

        return $setMethodCallArgs;
    }

    /**
     * @param Arg[][] $setValues
     */
    private function createArrayFromSetValues(array $setValues): Array_
    {
        $arrayItems = [];

        foreach ($setValues as $setValue) {
            if (count($setValue) > 1) {
                $arrayItems[] = new ArrayItem($setValue[1]->value, $setValue[0]->value);
            } elseif ($this->isCompactFuncCall($setValue[0]->value)) {
                /** @var FuncCall $compactFuncCall */
                $compactFuncCall = $setValue[0]->value;

                return $this->compactConverter->convertToArray($compactFuncCall);
            }
        }

        return new Array_($arrayItems);
    }

    private function isCompactFuncCall(Node $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        return $this->isName($node, 'compact');
    }

    private function hasRenderMethodCall(ClassMethod $classMethod): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($classMethod, function (Node $node) {
            return $this->isThisRenderMethodCall($node);
        });
    }

    private function completeImplicitRenderMethodCall(ClassMethod $classMethod): void
    {
        $methodCall = $this->createThisRenderMethodCall($classMethod);
        $return = new Return_($methodCall);

        $classMethod->stmts[] = $return;
    }

    private function isThisRenderMethodCall(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $this->isName($node->var, 'this')) {
            return false;
        }

        return $this->isName($node->name, 'render');
    }
}
