<?php

declare(strict_types=1);

namespace Rector\Sensio\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioTemplateTagValueNode;
use Rector\Core\PhpParser\Node\NodeFactory;
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

    public function __construct(NodeFactory $nodeFactory, TemplateGuesser $templateGuesser)
    {
        $this->nodeFactory = $nodeFactory;
        $this->templateGuesser = $templateGuesser;
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
    private function resolveArrayArgumentsFromMethodCall(Return_ $returnNode): array
    {
        if (! $returnNode->expr instanceof MethodCall) {
            return [];
        }

        $arguments = [];
        foreach ($returnNode->expr->args as $arg) {
            if (! $arg->value instanceof Array_) {
                continue;
            }

            $arguments[] = $arg->value;
        }

        return $arguments;
    }
}
