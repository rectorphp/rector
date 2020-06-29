<?php

declare(strict_types=1);

namespace Rector\Sensio\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Sensio\Helper\TemplateGuesser;

final class ThisRenderFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var TemplateGuesser
     */
    private $templateGuesser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ArrayFromCompactFactory
     */
    private $arrayFromCompactFactory;

    public function __construct(
        NodeFactory $nodeFactory,
        TemplateGuesser $templateGuesser,
        NodeNameResolver $nodeNameResolver,
        ArrayFromCompactFactory $arrayFromCompactFactory
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->templateGuesser = $templateGuesser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayFromCompactFactory = $arrayFromCompactFactory;
    }

    public function create(
        ClassMethod $classMethod,
        ?Return_ $return,
        SensioTemplateTagValueNode $sensioTemplateTagValueNode
    ): MethodCall {
        $renderArguments = $this->resolveRenderArguments($classMethod, $return, $sensioTemplateTagValueNode);

        return $this->nodeFactory->createMethodCall('this', 'render', $renderArguments);
    }

    /**
     * @return Arg[]
     */
    private function resolveRenderArguments(
        ClassMethod $classMethod,
        ?Return_ $return,
        SensioTemplateTagValueNode $sensioTemplateTagValueNode
    ): array {
        $arguments = [$this->resolveTemplateName($classMethod, $sensioTemplateTagValueNode)];
        if ($return === null) {
            return $this->nodeFactory->createArgs($arguments);
        }

        if ($return->expr instanceof Array_ && count($return->expr->items)) {
            $arguments[] = $return->expr;
        } elseif ($return->expr instanceof FuncCall && $this->nodeNameResolver->isName($return->expr, 'compact')) {
            /** @var FuncCall $compactFunCall */
            $compactFunCall = $return->expr;

            $array = $this->arrayFromCompactFactory->createArrayFromCompactFuncCall($compactFunCall);
            $arguments[1] = new Arg($array);
        } elseif ($sensioTemplateTagValueNode->getVars() !== []) {
            $variableList = $this->createArrayFromVars($sensioTemplateTagValueNode->getVars());
            $arguments[1] = new Arg($variableList);
        }

        $arguments = array_merge($arguments, $this->resolveArrayArgumentsFromMethodCall($return));

        return $this->nodeFactory->createArgs($arguments);
    }

    private function resolveTemplateName(
        ClassMethod $classMethod,
        SensioTemplateTagValueNode $sensioTemplateTagValueNode
    ): string {
        if ($sensioTemplateTagValueNode->getTemplate() !== null) {
            return $sensioTemplateTagValueNode->getTemplate();
        }

        return $this->templateGuesser->resolveFromClassMethodNode($classMethod);
    }

    /**
     * Already existing method call
     *
     * @return Array_[]
     */
    private function resolveArrayArgumentsFromMethodCall(Return_ $return): array
    {
        if (! $return->expr instanceof MethodCall) {
            return [];
        }

        $arguments = [];
        foreach ($return->expr->args as $arg) {
            if (! $arg->value instanceof Array_) {
                continue;
            }

            $arguments[] = $arg->value;
        }

        return $arguments;
    }

    /**
     * @param string[] $vars
     */
    private function createArrayFromVars(array $vars): Array_
    {
        $arrayItems = [];
        foreach ($vars as $var) {
            $arrayItems[] = new ArrayItem(new Variable($var), new String_($var));
        }

        return new Array_($arrayItems);
    }
}
