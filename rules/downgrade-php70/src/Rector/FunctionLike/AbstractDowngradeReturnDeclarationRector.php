<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp70\Contract\Rector\DowngradeReturnDeclarationRectorInterface;
use Traversable;

abstract class AbstractDowngradeReturnDeclarationRector extends AbstractRector implements DowngradeReturnDeclarationRectorInterface
{
    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    /**
     * @required
     */
    public function autowireAbstractDowngradeReturnDeclarationRector(PhpDocTypeChanger $phpDocTypeChanger): void
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
        if (! $this->shouldRemoveReturnDeclaration($node)) {
            return null;
        }

        $this->decorateFunctionLikeWithReturnTagValueNode($node);
        $node->returnType = null;

        return $node;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function decorateFunctionLikeWithReturnTagValueNode(FunctionLike $functionLike): void
    {
        if ($functionLike->returnType === null) {
            return;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);

        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($functionLike->returnType);
        if ($type instanceof IterableType) {
            $type = new UnionType([$type, new IntersectionType([new ObjectType(Traversable::class)])]);
        }

        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $type);
    }
}
