<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Naming;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\UnionType;
use Rector\NameImporting\ValueObject\NameAndParent;
use Rector\NodeNameResolver\NodeNameResolver;

final class NameRenamer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @param NameAndParent[] $namesAndParent
     */
    public function renameNameNode(array $namesAndParent, string $lastName): void
    {
        foreach ($namesAndParent as $nameAndParent) {
            $parentNode = $nameAndParent->getParentNode();
            $usedName = $nameAndParent->getNameNode();

            if ($parentNode instanceof TraitUse) {
                $this->renameTraitUse($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof Class_) {
                $this->renameClass($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof Param) {
                $this->renameParam($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof New_) {
                $this->renameNew($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof ClassMethod) {
                $this->renameClassMethod($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof Interface_) {
                $this->renameInterface($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof StaticCall) {
                $this->renameStaticCall($lastName, $parentNode);
            }

            if ($parentNode instanceof UnionType) {
                $this->renameUnionType($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof NullableType) {
                $this->renameNullableType($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof Instanceof_) {
                $this->renameInInstanceOf($lastName, $parentNode, $usedName);
            }

            if ($parentNode instanceof ClassConstFetch) {
                $this->renameClassConstFetch($lastName, $parentNode, $usedName);
            }
        }
    }

    private function renameClassConstFetch(
        string $lastName,
        ClassConstFetch $classConstFetch,
        Name | Identifier $usedNameNode
    ): void {
        if (! $this->nodeNameResolver->areNamesEqual($classConstFetch->class, $usedNameNode)) {
            return;
        }

        $classConstFetch->class = new Name($lastName);
    }

    private function renameInInstanceOf(
        string $lastName,
        Instanceof_ $instanceof,
        Name | Identifier $usedNameNode
    ): void {
        if (! $this->nodeNameResolver->areNamesEqual($instanceof->class, $usedNameNode)) {
            return;
        }

        $instanceof->class = new Name($lastName);
    }

    private function renameNullableType(
        string $lastName,
        NullableType $nullableType,
        Name | Identifier $usedNameNode
    ): void {
        if (! $this->nodeNameResolver->areNamesEqual($nullableType->type, $usedNameNode)) {
            return;
        }

        $nullableType->type = new Name($lastName);
    }

    private function renameTraitUse(string $lastName, TraitUse $traitUse, Name | Identifier $usedNameNode): void
    {
        foreach ($traitUse->traits as $key => $traitName) {
            if (! $this->nodeNameResolver->areNamesEqual($traitName, $usedNameNode)) {
                continue;
            }

            $traitUse->traits[$key] = new Name($lastName);
        }
    }

    private function renameClass(string $lastName, Class_ $class, Name | Identifier $usedNameNode): void
    {
        if ($class->name !== null && $this->nodeNameResolver->areNamesEqual($class->name, $usedNameNode)) {
            $class->name = new Identifier($lastName);
        }

        if ($class->extends !== null && $this->nodeNameResolver->areNamesEqual($class->extends, $usedNameNode)) {
            $class->extends = new Name($lastName);
        }

        foreach ($class->implements as $key => $implementNode) {
            if ($this->nodeNameResolver->areNamesEqual($implementNode, $usedNameNode)) {
                $class->implements[$key] = new Name($lastName);
            }
        }
    }

    private function renameParam(string $lastName, Param $param, Name | Identifier $usedNameNode): void
    {
        if ($param->type === null) {
            return;
        }

        if (! $this->nodeNameResolver->areNamesEqual($param->type, $usedNameNode)) {
            return;
        }

        $param->type = new Name($lastName);
    }

    private function renameUnionType(string $lastName, UnionType $unionType, Identifier|Name $usedNameNode): void
    {
        foreach ($unionType->types as $key => $unionedType) {
            if (! $this->nodeNameResolver->areNamesEqual($unionedType, $usedNameNode)) {
                continue;
            }

            if (! $unionedType instanceof Name) {
                continue;
            }

            $unionType->types[$key] = new Name($lastName);
        }
    }

    private function renameNew(string $lastName, New_ $new, Name | Identifier $usedNameNode): void
    {
        if ($this->nodeNameResolver->areNamesEqual($new->class, $usedNameNode)) {
            $new->class = new Name($lastName);
        }
    }

    private function renameClassMethod(
        string $lastName,
        ClassMethod $classMethod,
        Name | Identifier $usedNameNode
    ): void {
        if ($classMethod->returnType === null) {
            return;
        }

        if (! $this->nodeNameResolver->areNamesEqual($classMethod->returnType, $usedNameNode)) {
            return;
        }

        $classMethod->returnType = new Name($lastName);
    }

    private function renameInterface(string $lastName, Interface_ $interface, Name | Identifier $usedNameNode): void
    {
        foreach ($interface->extends as $key => $extendInterfaceName) {
            if (! $this->nodeNameResolver->areNamesEqual($extendInterfaceName, $usedNameNode)) {
                continue;
            }

            $interface->extends[$key] = new Name($lastName);
        }
    }

    private function renameStaticCall(string $lastName, StaticCall $staticCall): void
    {
        $staticCall->class = new Name($lastName);
    }
}
