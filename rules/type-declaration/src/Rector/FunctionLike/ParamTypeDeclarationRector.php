<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\ChildPopulator\ChildParamPopulator;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use Rector\TypeDeclaration\ValueObject\NewType;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\ParamTypeDeclarationRectorTest
 */
final class ParamTypeDeclarationRector extends AbstractTypeDeclarationRector
{
    /**
     * @var ParamTypeInferer
     */
    private $paramTypeInferer;

    /**
     * @var ChildParamPopulator
     */
    private $childParamPopulator;

    public function __construct(ParamTypeInferer $paramTypeInferer, ChildParamPopulator $childParamPopulator)
    {
        $this->paramTypeInferer = $paramTypeInferer;
        $this->childParamPopulator = $childParamPopulator;
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

        if ($inferedType instanceof ObjectType) {
            $fqcn = $inferedType instanceof ShortenedObjectType
                ? $inferedType->getFullyQualifiedName()
                : $inferedType->getClassName();
            $reflectionClass = new \ReflectionClass($fqcn);
            if ($reflectionClass->isTrait()) {
                return;
            }
        }

        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
            $inferedType,
            PHPStanStaticTypeMapper::KIND_PARAM
        );

        if ($paramTypeNode === null) {
            return;
        }

        $param->type = $paramTypeNode;
        $this->childParamPopulator->populateChildClassMethod($functionLike, $position, $inferedType);
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
        return ! $param->type->getAttribute(NewType::HAS_NEW_INHERITED_TYPE, false);
    }
}
