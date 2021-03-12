<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\NodeResolver;

use PhpParser\Node\Expr;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NetteCodeQuality\ValueObject\NetteFormMethodNameToControlType;

final class FormVariableInputNameTypeResolver
{
    /**
     * @var MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;

    public function __construct(MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver)
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }

    public function resolveControlTypeByInputName(Expr $formOrControlExpr, string $inputName): string
    {
        $methodNamesByInputNames = $this->methodNamesByInputNamesResolver->resolveExpr($formOrControlExpr);

        $formAddMethodName = $methodNamesByInputNames[$inputName] ?? null;
        if ($formAddMethodName === null) {
            $message = sprintf('Type was not found for "%s" input name', $inputName);
            throw new ShouldNotHappenException($message);
        }

        foreach (NetteFormMethodNameToControlType::METHOD_NAME_TO_CONTROL_TYPE as $methodName => $controlType) {
            if ($methodName !== $formAddMethodName) {
                continue;
            }

            return $controlType;
        }

        throw new NotImplementedYetException($formAddMethodName);
    }
}
