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
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp70\Contract\Rector\DowngradeParamDeclarationRectorInterface;
use Traversable;

abstract class AbstractDowngradeParamDeclarationRector extends AbstractRector implements DowngradeParamDeclarationRectorInterface
{
    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    /**
     * @required
     */
    public function autowireAbstractDowngradeParamDeclarationRector(PhpDocTypeChanger $phpDocTypeChanger): void
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }

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
        if ($param->type === null) {
            return;
        }

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        if ($type instanceof IterableType) {
            $type = new UnionType([$type, new IntersectionType([new ObjectType(Traversable::class)])]);
        }

        $paramName = $this->getName($param->var) ?? '';

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $type, $param, $paramName);
    }
}
