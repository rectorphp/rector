<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Downgrade\Rector\DowngradeRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Rector\FunctionLike\AbstractTypeDeclarationRector;

abstract class AbstractDowngradeReturnTypeDeclarationRector extends AbstractTypeDeclarationRector implements ConfigurableRectorInterface, DowngradeRectorInterface
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

        if ($this->shouldSkip($node)) {
            return null;
        }

        if ($this->addDocBlock) {
            /** @var PhpDocInfo|null $phpDocInfo */
            $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($phpDocInfo === null) {
                $phpDocInfo = $this->phpDocInfoFactory->createEmpty($node);
            }

            if (!is_null($node->returnType)) {
                $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->returnType);
                $phpDocInfo->changeReturnType($type);
            }
        }

        $node->returnType = null;

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->addDocBlock = $configuration[self::ADD_DOC_BLOCK] ?? true;
    }

    /**
     * Name of the type to remove
     */
    abstract protected function getReturnTypeName(): string;

    protected function getRectorDefinitionDescription(): string
    {
        return sprintf('Remove the \'%s\' function type, add a @return tag instead', $this->getReturnTypeName());
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function shouldSkip(FunctionLike $functionLike): bool
    {
        if ($functionLike->returnType === null) {
            return true;
        }

        // It can either be the type, or the nullable type (eg: ?object)
        $isNullableType = $functionLike->returnType instanceof NullableType;
        $typeName = $isNullableType ? $this->getName($functionLike->returnType->type) : $this->getName($functionLike->returnType);

        // Check it is the type to be removed
        return $typeName !== $this->getReturnTypeName();
    }
}
