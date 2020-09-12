<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DowngradePhp71\Contract\Rector\DowngradeReturnDeclarationRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractDowngradeReturnDeclarationRector extends AbstractDowngradeRector implements DowngradeReturnDeclarationRectorInterface
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
        if (! $this->shouldRemoveReturnDeclaration($node)) {
            return null;
        }

        if ($this->addDocBlock) {
            /** @var PhpDocInfo|null $phpDocInfo */
            $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($phpDocInfo === null) {
                $phpDocInfo = $this->phpDocInfoFactory->createEmpty($node);
            }

            if ($node->returnType !== null) {
                $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->returnType);
                $phpDocInfo->changeReturnType($type);
            }
        }

        $node->returnType = null;

        return $node;
    }
}
