<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\ZendToSymfony\Detector\ZendDetector;
use Rector\ZendToSymfony\ValueObject\ZendClass;

/**
 * @sponsor Thanks https://previo.cz/ for sponsoring this rule
 *
 * @see \Rector\ZendToSymfony\Tests\Rector\ClassMethod\ThisViewToThisRenderResponseRector\ThisViewToThisRenderResponseRectorTest
 */
final class ThisViewToThisRenderResponseRector extends AbstractRector
{
    /**
     * @var string
     */
    private const TEMPLATE_DATA_VARIABLE_NAME = 'templateData';

    /**
     * @var ZendDetector
     */
    private $zendDetector;

    public function __construct(ZendDetector $zendDetector)
    {
        $this->zendDetector = $zendDetector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change $this->_view->assign = 5; to $this->render("...", $templateData);',
            [new CodeSample(
                <<<'PHP'
public function someAction()
{
    $this->_view->value = 5;
}
PHP
                ,
                <<<'PHP'
public function someAction()
{
    $templateData = [];
    $templateData['value']; = 5;
    
    return $this->render("...", $templateData);
}                
PHP
            )]
        );
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
        if (! $this->zendDetector->isZendActionMethod($node)) {
            return null;
        }

        $hasRender = false;
        $this->traverseNodesWithCallable((array) $node->stmts, function (Node $node) use (&$hasRender): ?Assign {
            if (! $node instanceof Assign) {
                return null;
            }

            $assignedVariable = $node->var;
            if (! $assignedVariable instanceof PropertyFetch) {
                return null;
            }

            if (! $assignedVariable->var instanceof PropertyFetch) {
                return null;
            }

            if (! $this->isZendViewPropertyFetch($assignedVariable->var)) {
                return null;
            }

            $hasRender = true;

            $node->var = $this->createTemplateDataVariableArrayDimFetch($assignedVariable);

            return $node;
        });

        if (! $hasRender) {
            return null;
        }

        $node->stmts = array_merge(
            [$this->createTemplateDataInitAssign()],
            (array) $node->stmts,
            [$this->createRenderMethodCall()]
        );

        return $node;
    }

    private function isZendViewPropertyFetch(PropertyFetch $propertyFetch): bool
    {
        if ($this->isObjectType($propertyFetch, ZendClass::ZEND_VIEW)) {
            return true;
        }
        // fallback if checked property is missing @var doc
        return $this->isNames($propertyFetch->name, ['_view', 'view']);
    }

    /**
     * Creates:
     * $this->value->variableName;
     * ↓
     * $templateData['variableName'];
     */
    private function createTemplateDataVariableArrayDimFetch(PropertyFetch $propertyFetch): ArrayDimFetch
    {
        $templateDataVariable = new Variable(self::TEMPLATE_DATA_VARIABLE_NAME);

        /** @var string $variableName */
        $variableName = $this->getName($propertyFetch->name);

        return new ArrayDimFetch($templateDataVariable, new String_($variableName));
    }

    private function createTemplateDataInitAssign(): Expression
    {
        $assign = new Assign(new Variable(self::TEMPLATE_DATA_VARIABLE_NAME), new Array_([]));

        return new Expression($assign);
    }

    private function createRenderMethodCall(): Expression
    {
        $arguments = [new String_('...'), new Variable(self::TEMPLATE_DATA_VARIABLE_NAME)];

        $methodCall = $this->createMethodCall('this', 'render', $arguments);

        return new Expression($methodCall);
    }
}
