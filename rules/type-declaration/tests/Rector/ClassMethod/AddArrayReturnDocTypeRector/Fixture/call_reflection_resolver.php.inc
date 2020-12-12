<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;

class CallReflectionResolverFixture
{
    public function resolveCall(Node $node)
    {
        if ($node instanceof FuncCall) {
            return $this->resolveFunctionCall($node);
        }

        return $this->resolveMethodCall($node);
    }

    /**
     * @return FunctionReflection|MethodReflection|null
     */
    private function resolveFunctionCall(FuncCall $funcCall)
    {
    }

    private function resolveMethodCall(Node $node): ?MethodReflection
    {
    }

}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;

class CallReflectionResolverFixture
{
    /**
     * @return \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection|null
     */
    public function resolveCall(Node $node)
    {
        if ($node instanceof FuncCall) {
            return $this->resolveFunctionCall($node);
        }

        return $this->resolveMethodCall($node);
    }

    /**
     * @return FunctionReflection|MethodReflection|null
     */
    private function resolveFunctionCall(FuncCall $funcCall)
    {
    }

    private function resolveMethodCall(Node $node): ?MethodReflection
    {
    }

}
?>
