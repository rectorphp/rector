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
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\CakePHPToSymfony\Rector\AbstractCakePHPRector;
use Rector\CakePHPToSymfony\Rector\TemplatePathResolver;
use Rector\CodeQuality\CompactConverter;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://book.cakephp.org/2/en/tutorials-and-examples/blog/part-two.html
 * @see https://symfony.com/doc/5.0/controller.html
 * @see https://symfony.com/doc/5.0/controller.html#rendering-templates
 *
 * @see https://stackoverflow.com/a/21647715/1348344 for $this->view
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector\CakePHPControllerActionToSymfonyControllerActionRectorTest
 */
final class CakePHPControllerActionToSymfonyControllerActionRector extends AbstractCakePHPRector
{
    /**
     * @var CompactConverter
     */
    private $compactConverter;

    /**
     * @var TemplatePathResolver
     */
    private $templatePathResolver;

    public function __construct(CompactConverter $compactConverter, TemplatePathResolver $templatePathResolver)
    {
        $this->compactConverter = $compactConverter;
        $this->templatePathResolver = $templatePathResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP 2.4 Controller action to Symfony 5', [
            new CodeSample(
                <<<'PHP'
class HomepageController extends \AppController
{
    public function index()
    {
        $value = 5;
        $this->set('name', $value);
    }
}
PHP
,
                <<<'PHP'
use Symfony\Component\HttpFoundation\Response;

class HomepageController extends \AppController
{
    public function index(): Response
    {
        $value = 5;
        return $this->renderResponse('homepage/index.twig', [
            'name' => $value
        ]);
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

        if (! $node->isPublic()) {
            return null;
        }

        $methodCall = $this->createThisRenderMethodCall($node);
        $return = new Return_($methodCall);
        $node->stmts[] = $return;

        $node->returnType = new FullyQualified('Symfony\Component\HttpFoundation\Response');

        return $node;
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
}
