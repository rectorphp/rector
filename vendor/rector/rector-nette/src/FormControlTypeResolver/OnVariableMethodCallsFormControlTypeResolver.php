<?php

declare (strict_types=1);
namespace Rector\Nette\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\MethodCallManipulator;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Nette\Contract\FormControlTypeResolverInterface;
use Rector\Nette\ValueObject\NetteFormMethodNameToControlType;
use Rector\NodeNameResolver\NodeNameResolver;
final class OnVariableMethodCallsFormControlTypeResolver implements \Rector\Nette\Contract\FormControlTypeResolverInterface
{
    /**
     * @var \Rector\Core\NodeManipulator\MethodCallManipulator
     */
    private $methodCallManipulator;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\Rector\Core\NodeManipulator\MethodCallManipulator $methodCallManipulator, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->methodCallManipulator = $methodCallManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @return array<string, string>
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : array
    {
        if (!$node instanceof \PhpParser\Node\Expr\Variable) {
            return [];
        }
        $onFormMethodCalls = $this->methodCallManipulator->findMethodCallsOnVariable($node);
        $methodNamesByInputNames = [];
        foreach ($onFormMethodCalls as $onFormMethodCall) {
            $methodName = $this->nodeNameResolver->getName($onFormMethodCall->name);
            if ($methodName === null) {
                continue;
            }
            if (!isset(\Rector\Nette\ValueObject\NetteFormMethodNameToControlType::METHOD_NAME_TO_CONTROL_TYPE[$methodName])) {
                continue;
            }
            if (!isset($onFormMethodCall->args[0])) {
                continue;
            }
            $addedInputName = $this->valueResolver->getValue($onFormMethodCall->args[0]->value);
            if (!\is_string($addedInputName)) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $methodNamesByInputNames[$addedInputName] = $methodName;
        }
        return $methodNamesByInputNames;
    }
}
