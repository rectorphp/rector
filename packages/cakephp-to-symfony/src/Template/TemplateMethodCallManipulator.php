<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Template;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\CakePHPToSymfony\TemplatePathResolver;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;

final class TemplateMethodCallManipulator
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var TemplatePathResolver
     */
    private $templatePathResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        ValueResolver $valueResolver,
        TemplatePathResolver $templatePathResolver,
        CallableNodeTraverser $callableNodeTraverser,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->valueResolver = $valueResolver;
        $this->templatePathResolver = $templatePathResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function refactorExistingRenderMethodCall(ClassMethod $classMethod): void
    {
        $controllerNamePart = $this->templatePathResolver->resolveClassNameTemplatePart($classMethod);

        $this->callableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use (
            $controllerNamePart
        ) {
            $renderMethodCall = $this->matchThisRenderMethodCallBareOrInReturn($node);
            if ($renderMethodCall === null) {
                return null;
            }

            return $this->refactorRenderTemplateName($renderMethodCall, $controllerNamePart);
        });
    }

    private function matchThisRenderMethodCallBareOrInReturn(Node $node): ?MethodCall
    {
        if ($node instanceof Return_) {
            $nodeExpr = $node->expr;
            if ($nodeExpr === null) {
                return null;
            }

            if (! $this->isThisRenderMethodCall($nodeExpr)) {
                return null;
            }

            /** @var MethodCall $nodeExpr */
            return $nodeExpr;
        }

        if ($node instanceof Expression) {
            if (! $this->isThisRenderMethodCall($node->expr)) {
                return null;
            }

            return $node->expr;
        }

        return null;
    }

    private function refactorRenderTemplateName(Node $node, string $controllerNamePart): ?Return_
    {
        /** @var MethodCall $node */
        $renderArgumentValue = $this->valueResolver->getValue($node->args[0]->value);

        /** @var string|mixed $renderArgumentValue */
        if (! is_string($renderArgumentValue)) {
            return null;
        }

        if (Strings::contains($renderArgumentValue, '/')) {
            $templateName = $renderArgumentValue . '.twig';
        } else {
            // add explicit controller
            $templateName = $controllerNamePart . '/' . $renderArgumentValue . '.twig';
        }

        $node->args[0]->value = new String_($templateName);

        return new Return_($node);
    }

    private function isThisRenderMethodCall(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($node->var, 'this')) {
            return false;
        }

        return $this->nodeNameResolver->isName($node->name, 'render');
    }
}
