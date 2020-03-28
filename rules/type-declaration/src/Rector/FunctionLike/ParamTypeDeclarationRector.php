<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\ParamTypeDeclarationRectorTest
 */
final class ParamTypeDeclarationRector extends AbstractTypeDeclarationRector
{
    /**
     * @var ParamTypeInferer
     */
    private $paramTypeInferer;

    public function __construct(ParamTypeInferer $paramTypeInferer)
    {
        $this->paramTypeInferer = $paramTypeInferer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change @param types to type declarations if not a BC-break', [
            new CodeSample(
                <<<'PHP'
<?php

class ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}

final class ChildClass extends ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }

    /**
     * @param int $number
     */
    public function change($number)
    {
    }
}
PHP
                ,
                <<<'PHP'
<?php

class ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}

final class ChildClass extends ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }

    /**
     * @param int $number
     */
    public function change(int $number)
    {
    }
}
PHP
            ),
        ]);
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }

        if (empty($node->params)) {
            return null;
        }

        foreach ($node->params as $position => $param) {
            $this->refactorParam($param, $node, (int) $position);
        }

        return null;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function refactorParam(Param $param, FunctionLike $functionLike, int $position): void
    {
        if ($this->shouldSkipParam($param, $functionLike, $position)) {
            return;
        }

        $inferedType = $this->paramTypeInferer->inferParam($param);
        if ($inferedType instanceof MixedType) {
            return;
        }

        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
            $inferedType,
            PHPStanStaticTypeMapper::KIND_PARAM
        );

        if ($paramTypeNode === null) {
            return;
        }

        $param->type = $paramTypeNode;
        $this->populateChildren($functionLike, $position, $inferedType);
    }

    /**
     * Add typehint to all children
     * @param ClassMethod|Function_ $functionLike
     */
    private function populateChildren(FunctionLike $functionLike, int $position, Type $paramType): void
    {
        if (! $functionLike instanceof ClassMethod) {
            return;
        }

        /** @var string|null $className */
        $className = $functionLike->getAttribute(AttributeKey::CLASS_NAME);
        // anonymous class
        if ($className === null) {
            return;
        }

        $childrenClassLikes = $this->classLikeParsedNodesFinder->findClassesAndInterfacesByType($className);

        // update their methods as well
        foreach ($childrenClassLikes as $childClassLike) {
            if ($childClassLike instanceof Class_) {
                $usedTraits = $this->classLikeParsedNodesFinder->findUsedTraitsInClass($childClassLike);

                foreach ($usedTraits as $trait) {
                    $this->addParamTypeToMethod($trait, $position, $functionLike, $paramType);
                }
            }

            $this->addParamTypeToMethod($childClassLike, $position, $functionLike, $paramType);
        }
    }

    private function addParamTypeToMethod(
        ClassLike $classLike,
        int $position,
        ClassMethod $classMethod,
        Type $paramType
    ): void {
        $methodName = $this->getName($classMethod);

        $currentClassMethod = $classLike->getMethod($methodName);
        if ($currentClassMethod === null) {
            return;
        }

        if (! isset($currentClassMethod->params[$position])) {
            return;
        }

        $paramNode = $currentClassMethod->params[$position];

        // already has a type
        if ($paramNode->type !== null) {
            return;
        }

        $resolvedChildType = $this->resolveChildTypeNode($paramType);
        if ($resolvedChildType === null) {
            return;
        }

        // let the method know it was changed now
        $paramNode->type = $resolvedChildType;
        $paramNode->type->setAttribute(self::HAS_NEW_INHERITED_TYPE, true);

        $this->notifyNodeFileInfo($paramNode);
    }

    private function shouldSkipParam(Param $param, FunctionLike $functionLike, int $position): bool
    {
        if ($this->vendorLockResolver->isClassMethodParamLockedIn($functionLike, $position)) {
            return true;
        }

        if ($param->variadic) {
            return true;
        }

        // no type → check it
        if ($param->type === null) {
            return false;
        }

        // already set → skip
        return ! $param->type->getAttribute(self::HAS_NEW_INHERITED_TYPE, false);
    }
}
