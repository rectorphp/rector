<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Downgrade\Rector\DowngradeRectorTrait;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\TypeDeclaration\Rector\FunctionLike\AbstractTypeDeclarationRector;

abstract class AbstractDowngradeReturnTypeDeclarationRector extends AbstractTypeDeclarationRector implements ConfigurableRectorInterface
{
    use DowngradeRectorTrait;

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

            $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->returnType);
            $phpDocInfo->changeReturnType($type);
        }

        $node->returnType = null;

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->addDocBlock = $configuration[self::ADD_DOC_BLOCK] ?? true;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function shouldSkip(FunctionLike $functionLike): bool
    {
        if ($functionLike->returnType === null) {
            return true;
        }

        return $functionLike->returnType->name != $this->getReturnTypeName();
    }

    /**
     * Name of the type to remove
     */
    abstract protected function getReturnTypeName(): string;

    protected function getRectorDefinitionDescription(): string
    {
        return sprintf(
            'Remove the \'%s\' function type, add a @return tag instead',
            $this->getReturnTypeName()
        );
    }
}
