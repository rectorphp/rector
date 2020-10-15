<?php

declare(strict_types=1);

namespace Rector\Nette\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
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
        if ($magicTemplatePropertyCalls->getTemplateFileExpr() !== null) {
            $methodCall->args[0] = new Arg($magicTemplatePropertyCalls->getTemplateFileExpr());
        }

        if ($magicTemplatePropertyCalls->getTemplateVariables() !== []) {
            $templateVariablesArray = $this->createTemplateVariablesArray(
                $magicTemplatePropertyCalls->getTemplateVariables()
            );

            $methodCall->args[1] = new Arg($templateVariablesArray);
        }
    }

    /**
     * @param Expr[] $templateVariables
     */
    private function createTemplateVariablesArray(array $templateVariables): Array_
    {
        $array = new Array_();
        foreach ($templateVariables as $name => $node) {
            $array->items[] = new ArrayItem($node, new String_($name));
        }

        return $array;
    }
}
