<?php

declare(strict_types=1);

namespace Rector\Nette\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Nette\ValueObject\MagicTemplatePropertyCalls;

final class ActionRenderFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function createThisRenderMethodCall(MagicTemplatePropertyCalls $magicTemplatePropertyCalls): MethodCall
    {
        $methodCall = $this->nodeFactory->createMethodCall('this', 'render');
        $this->addArguments($magicTemplatePropertyCalls, $methodCall);

        return $methodCall;
    }

    public function createThisTemplateRenderMethodCall(
        MagicTemplatePropertyCalls $magicTemplatePropertyCalls
    ): MethodCall {
        $thisTemplatePropertyFetch = new PropertyFetch(new Variable('this'), 'template');
        $methodCall = $this->nodeFactory->createMethodCall($thisTemplatePropertyFetch, 'render');

        $this->addArguments($magicTemplatePropertyCalls, $methodCall);

        return $methodCall;
    }

    private function addArguments(
        MagicTemplatePropertyCalls $magicTemplatePropertyCalls,
        MethodCall $methodCall
    ): void {
        if ($magicTemplatePropertyCalls->getFirstTemplateFileExpr() !== null) {
            $methodCall->args[0] = new Arg($magicTemplatePropertyCalls->getFirstTemplateFileExpr());
        }

        $templateVariablesArray = $this->createTemplateVariablesArray($magicTemplatePropertyCalls);
        if ($templateVariablesArray->items === []) {
            return;
        }

        $methodCall->args[1] = new Arg($templateVariablesArray);
    }

    private function createTemplateVariablesArray(MagicTemplatePropertyCalls $magicTemplatePropertyCalls): Array_
    {
        $array = new Array_();
        foreach ($magicTemplatePropertyCalls->getTemplateVariables() as $name => $expr) {
            $array->items[] = new ArrayItem($expr, new String_($name));
        }

        foreach ($magicTemplatePropertyCalls->getConditionalVariableNames() as $variableName) {
            $array->items[] = new ArrayItem(new Variable($variableName), new String_($variableName));
        }

        return $array;
    }
}
