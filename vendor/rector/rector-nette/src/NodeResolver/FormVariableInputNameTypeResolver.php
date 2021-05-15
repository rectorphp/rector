<?php

declare (strict_types=1);
namespace Rector\Nette\NodeResolver;

use PhpParser\Node\Expr;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Nette\ValueObject\NetteFormMethodNameToControlType;
final class FormVariableInputNameTypeResolver
{
    /**
     * @var \Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;
    public function __construct(\Rector\Nette\NodeResolver\MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver)
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }
    public function resolveControlTypeByInputName(\PhpParser\Node\Expr $formOrControlExpr, string $inputName) : string
    {
        $methodNamesByInputNames = $this->methodNamesByInputNamesResolver->resolveExpr($formOrControlExpr);
        $formAddMethodName = $methodNamesByInputNames[$inputName] ?? null;
        if ($formAddMethodName === null) {
            $message = \sprintf('Type was not found for "%s" input name', $inputName);
            throw new \Rector\Core\Exception\ShouldNotHappenException($message);
        }
        foreach (\Rector\Nette\ValueObject\NetteFormMethodNameToControlType::METHOD_NAME_TO_CONTROL_TYPE as $methodName => $controlType) {
            if ($methodName !== $formAddMethodName) {
                continue;
            }
            return $controlType;
        }
        throw new \Rector\Core\Exception\NotImplementedYetException($formAddMethodName);
    }
}
