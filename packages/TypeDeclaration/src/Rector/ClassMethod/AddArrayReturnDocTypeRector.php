<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInferer;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\AddArrayReturnDocTypeRectorTest
 */
final class AddArrayReturnDocTypeRector extends AbstractRector
{
    /**
     * @var int
     */
    private const MAX_NUMBER_OF_TYPES = 3;

    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    public function __construct(ReturnTypeInferer $returnTypeInferer)
    {
        $this->returnTypeInferer = $returnTypeInferer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds @return annotation to array parameters inferred from the rest of the code', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    public function getValues(): array
    {
        return $this->values;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $inferedType = $this->returnTypeInferer->inferFunctionLikeWithExcludedInferers(
            $node,
            [ReturnTypeDeclarationReturnTypeInferer::class]
        );

        if ($this->shouldSkipType($inferedType, $node)) {
            return null;
        }

        $this->docBlockManipulator->addReturnTag($node, $inferedType);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if ($this->isName($classMethod->name, '__*')) {
            return true;
        }

        if ($classMethod->returnType !== null) {
            if (! $this->isNames($classMethod->returnType, ['array', 'iterable'])) {
                return true;
            }
        }

        return false;
    }

    private function shouldSkipType(Type $newType, ClassMethod $classMethod): bool
    {
        if ($newType instanceof ArrayType) {
            if ($this->shouldSkipArrayType($newType, $classMethod)) {
                return true;
            }
        }

        if ($newType instanceof UnionType) {
            if (count($newType->getTypes()) > self::MAX_NUMBER_OF_TYPES) {
                return true;
            }
        }

        if ($newType instanceof ConstantArrayType) {
            if (count($newType->getValueTypes()) > self::MAX_NUMBER_OF_TYPES) {
                return true;
            }
        }

        return false;
    }

    private function isNewAndCurrentTypeBothCallable(ArrayType $newArrayType, ClassMethod $classMethod): bool
    {
        $currentPhpDocInfo = $this->getPhpDocInfo($classMethod);
        if ($currentPhpDocInfo === null) {
            return false;
        }

        $currentReturnType = $currentPhpDocInfo->getReturnType();
        if (! $currentReturnType instanceof ArrayType) {
            return false;
        }

        if (! $newArrayType->getItemType()->isCallable()->yes()) {
            return false;
        }

        if (! $currentReturnType->getItemType()->isCallable()->yes()) {
            return false;
        }

        return true;
    }

    private function isMixedOfSpecificOverride(ArrayType $arrayType, ClassMethod $classMethod): bool
    {
        if (! $arrayType->getItemType() instanceof MixedType) {
            return false;
        }

        $currentPhpDocInfo = $this->getPhpDocInfo($classMethod);
        if ($currentPhpDocInfo === null) {
            return false;
        }

        $currentReturnType = $currentPhpDocInfo->getReturnType();
        if (! $currentReturnType instanceof ArrayType) {
            return false;
        }

        return true;
    }

    private function shouldSkipArrayType(ArrayType $arrayType, ClassMethod $classMethod): bool
    {
        if ($this->isNewAndCurrentTypeBothCallable($arrayType, $classMethod)) {
            return true;
        }

        if ($this->isMixedOfSpecificOverride($arrayType, $classMethod)) {
            return true;
        }

        return false;
    }
}
