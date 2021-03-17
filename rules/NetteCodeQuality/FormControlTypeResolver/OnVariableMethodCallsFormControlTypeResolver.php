<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\MethodCallManipulator;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NetteCodeQuality\Contract\FormControlTypeResolverInterface;
use Rector\NetteCodeQuality\ValueObject\NetteFormMethodNameToControlType;
use Rector\NodeNameResolver\NodeNameResolver;

final class OnVariableMethodCallsFormControlTypeResolver implements FormControlTypeResolverInterface
{
    /**
     * @var MethodCallManipulator
     */
    private $methodCallManipulator;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(
        MethodCallManipulator $methodCallManipulator,
        NodeNameResolver $nodeNameResolver,
        ValueResolver $valueResolver
    ) {
        $this->methodCallManipulator = $methodCallManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
    }

    /**
     * @return array<string, string>
     */
    public function resolve(Node $node): array
    {
        if (! $node instanceof Variable) {
            return [];
        }

        $onFormMethodCalls = $this->methodCallManipulator->findMethodCallsOnVariable($node);

        $methodNamesByInputNames = [];
        foreach ($onFormMethodCalls as $onFormMethodCall) {
            $methodName = $this->nodeNameResolver->getName($onFormMethodCall->name);
            if ($methodName === null) {
                continue;
            }

            if (! isset(NetteFormMethodNameToControlType::METHOD_NAME_TO_CONTROL_TYPE[$methodName])) {
                continue;
            }

            if (! isset($onFormMethodCall->args[0])) {
                continue;
            }

            $addedInputName = $this->valueResolver->getValue($onFormMethodCall->args[0]->value);
            if (! is_string($addedInputName)) {
                throw new ShouldNotHappenException();
            }

            $methodNamesByInputNames[$addedInputName] = $methodName;
        }

        return $methodNamesByInputNames;
    }
}
