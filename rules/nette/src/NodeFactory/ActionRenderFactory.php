<?php

declare(strict_types=1);

namespace Rector\Nette\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NetteToSymfony\ValueObject\ClassMethodRender;

final class ActionRenderFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var RenderParameterArrayFactory
     */
    private $renderParameterArrayFactory;

    public function __construct(
        NodeFactory $nodeFactory,
        \Rector\Nette\NodeFactory\RenderParameterArrayFactory $renderParameterArrayFactory
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->renderParameterArrayFactory = $renderParameterArrayFactory;
    }

    public function createThisRenderMethodCall(ClassMethodRender $classMethodRender): MethodCall
    {
        $methodCall = $this->nodeFactory->createMethodCall('this', 'render');
        $this->addArguments($classMethodRender, $methodCall);

        return $methodCall;
    }

    public function createThisTemplateRenderMethodCall(ClassMethodRender $classMethodRender): MethodCall
    {
        $thisTemplatePropertyFetch = new PropertyFetch(new Variable('this'), 'template');
        $methodCall = $this->nodeFactory->createMethodCall($thisTemplatePropertyFetch, 'render');

        $this->addArguments($classMethodRender, $methodCall);

        return $methodCall;
    }

    private function addArguments(ClassMethodRender $classMethodRender, MethodCall $methodCall): void
    {
        if ($classMethodRender->getFirstTemplateFileExpr() !== null) {
            $methodCall->args[0] = new Arg($classMethodRender->getFirstTemplateFileExpr());
        }

        $templateVariablesArray = $this->renderParameterArrayFactory->createArray($classMethodRender);
        if (! $templateVariablesArray instanceof Array_) {
            return;
        }

        $methodCall->args[1] = new Arg($templateVariablesArray);
    }
}
