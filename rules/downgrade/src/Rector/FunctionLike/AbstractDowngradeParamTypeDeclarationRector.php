<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Identifier;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\Rector\FunctionLike\AbstractTypeDeclarationRector;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractDowngradeParamTypeDeclarationRector extends AbstractTypeDeclarationRector
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

            $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
            $phpDocInfo->changeParamType($type, $param, $param->var->name);
        }

        $param->type = null;
    }

    private function shouldSkipParam(Param $param, FunctionLike $functionLike, int $position): bool
    {
        if ($this->vendorLockResolver->isClassMethodParamLockedIn($functionLike, $position)) {
            return true;
        }

        if ($param->variadic) {
            return true;
        }

        if ($param->type === null) {
            return true;
        }

        if (!($param->type instanceof Identifier)) {
            return true;
        }

        // Check it is the type to be removed
        return $param->type->name != $this->getParamTypeName();
    }

    /**
     * Name of the type to remove
     */
    abstract protected function getParamTypeName(): string;

    protected function getRectorDefinitionDescription(): string
    {
        return sprintf(
            'Remove the \'%s\' param type, add a @param tag instead',
            $this->getParamTypeName()
        );
    }
}
