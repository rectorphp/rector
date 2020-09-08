<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\FunctionLike;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Downgrade\Contract\Rector\DowngradeTypeRectorInterface;

abstract class AbstractDowngradeReturnTypeDeclarationRector extends AbstractDowngradeReturnDeclarationRector implements DowngradeTypeRectorInterface
{
    /**
     * @param ClassMethod|Function_ $functionLike
     */
    public function shouldRemoveReturnDeclaration(FunctionLike $functionLike): bool
    {
        if ($functionLike->returnType === null) {
            return false;
        }

        // It can either be the type, or the nullable type (eg: ?object)
        $isNullableType = $functionLike->returnType instanceof NullableType;
        if ($isNullableType) {
            /** @var NullableType */
            $nullableType = $functionLike->returnType;
            $typeName = $this->getName($nullableType->type);
        } else {
            $typeName = $this->getName($functionLike->returnType);
        }

        // Check it is the type to be removed
        return $typeName === $this->getTypeNameToRemove();
    }

    protected function getRectorDefinitionDescription(): string
    {
        return sprintf("Remove the '%s' function type, add a @return tag instead", $this->getTypeNameToRemove());
    }
}
