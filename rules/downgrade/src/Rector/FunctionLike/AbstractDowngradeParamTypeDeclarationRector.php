<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Downgrade\Contract\Rector\DowngradeRectorInterface;
use Rector\Downgrade\Contract\Rector\DowngradeTypeRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Rector\FunctionLike\AbstractTypeDeclarationRector;

abstract class AbstractDowngradeParamTypeDeclarationRector extends AbstractTypeDeclarationRector implements ConfigurableRectorInterface, DowngradeRectorInterface, DowngradeTypeRectorInterface
{
    /**
     * @var string
     */
    public const ADD_DOC_BLOCK = '$addDocBlock';

    /**
     * @var bool
     */
    private $addDocBlock = true;

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isAtLeastPhpVersion($this->getPhpVersionFeature())) {
            return $node;
        }

        if ($node->params === null || $node->params === []) {
            return null;
        }

        foreach ($node->params as $position => $param) {
            $this->refactorParam($param, $node, (int) $position);
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->addDocBlock = $configuration[self::ADD_DOC_BLOCK] ?? true;
    }

    protected function getRectorDefinitionDescription(): string
    {
        return sprintf("Remove the '%s' param type, add a @param tag instead", $this->getTypeNameToRemove());
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function refactorParam(Param $param, FunctionLike $functionLike, int $position): void
    {
        if ($this->shouldSkipParam($param, $functionLike, $position)) {
            return;
        }

        if ($this->addDocBlock) {
            $node = $functionLike;
            /** @var PhpDocInfo|null $phpDocInfo */
            $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($phpDocInfo === null) {
                $phpDocInfo = $this->phpDocInfoFactory->createEmpty($node);
            }

            if ($param->type !== null) {
                $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
                $paramName = $this->getName($param->var) ?? '';
                $phpDocInfo->changeParamType($type, $param, $paramName);
            }
        }

        $param->type = null;
    }

    private function shouldSkipParam(Param $param, FunctionLike $functionLike, int $position): bool
    {
        if ($param->variadic) {
            return true;
        }

        if ($param->type === null) {
            return true;
        }

        // It can either be the type, or the nullable type (eg: ?object)
        $isNullableType = $param->type instanceof NullableType;
        if (! $param->type instanceof Identifier && ! $isNullableType) {
            return true;
        }

        // If it is the NullableType, extract the name from its inner type
        if ($isNullableType) {
            /** @var NullableType */
            $nullableType = $param->type;
            $typeName = $this->getName($nullableType->type);
        } else {
            $typeName = $this->getName($param->type);
        }

        // Check it is the type to be removed
        return $typeName !== $this->getTypeNameToRemove();
    }
}
