<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp70\Contract\Rector\DowngradeParamDeclarationRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Traversable;

abstract class AbstractDowngradeParamDeclarationRector extends AbstractRector implements DowngradeParamDeclarationRectorInterface
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->params === null) {
            return null;
        }
        if ($node->params === []) {
            return null;
        }
        foreach ($node->params as $param) {
            $this->refactorParam($param, $node);
        }

        return null;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function refactorParam(Param $param, FunctionLike $functionLike): void
    {
        if (! $this->shouldRemoveParamDeclaration($param, $functionLike)) {
            return;
        }

        $this->decorateWithDocBlock($functionLike, $param);

        $param->type = null;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function decorateWithDocBlock(FunctionLike $functionLike, Param $param): void
    {
        $node = $functionLike;
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($node);
        }

        if ($param->type !== null) {
            $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);

            if ($type instanceof IterableType) {
                $type = new UnionType([$type, new IntersectionType([new ObjectType(Traversable::class)])]);
            }

            $paramName = $this->getName($param->var) ?? '';
            $phpDocInfo->changeParamType($type, $param, $paramName);
        }
    }
}
