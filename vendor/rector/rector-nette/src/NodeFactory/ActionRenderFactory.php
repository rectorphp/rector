<?php

declare (strict_types=1);
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
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\Nette\NodeFactory\RenderParameterArrayFactory $renderParameterArrayFactory)
    {
        $this->nodeFactory = $nodeFactory;
        $this->renderParameterArrayFactory = $renderParameterArrayFactory;
    }
    public function createThisRenderMethodCall(\Rector\NetteToSymfony\ValueObject\ClassMethodRender $classMethodRender) : \PhpParser\Node\Expr\MethodCall
    {
        $methodCall = $this->nodeFactory->createMethodCall('this', 'render');
        $this->addArguments($classMethodRender, $methodCall);
        return $methodCall;
    }
    public function createThisTemplateRenderMethodCall(\Rector\NetteToSymfony\ValueObject\ClassMethodRender $classMethodRender) : \PhpParser\Node\Expr\MethodCall
    {
        $thisTemplatePropertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), 'template');
        $methodCall = $this->nodeFactory->createMethodCall($thisTemplatePropertyFetch, 'render');
        $this->addArguments($classMethodRender, $methodCall);
        return $methodCall;
    }
    private function addArguments(\Rector\NetteToSymfony\ValueObject\ClassMethodRender $classMethodRender, \PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        if ($classMethodRender->getFirstTemplateFileExpr() !== null) {
            $methodCall->args[0] = new \PhpParser\Node\Arg($classMethodRender->getFirstTemplateFileExpr());
        }
        $templateVariablesArray = $this->renderParameterArrayFactory->createArray($classMethodRender);
        if (!$templateVariablesArray instanceof \PhpParser\Node\Expr\Array_) {
            return;
        }
        $methodCall->args[1] = new \PhpParser\Node\Arg($templateVariablesArray);
    }
}
