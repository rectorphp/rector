<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc;

use Rector\AttributeAwarePhpDoc\Contract\DecoratingNodeFactoryInterface;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class DecoratingNodeFactory
{
    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @var DecoratingNodeFactoryInterface[]
     */
    private $decoratingNodeFactories = [];

    /**
     * @param DecoratingNodeFactoryInterface[] $decoratingNodeFactories
     */
    public function __construct(PhpDocNodeTraverser $phpDocNodeTraverser, array $decoratingNodeFactories)
    {
        $this->decoratingNodeFactories = $decoratingNodeFactories;
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
    }

    /**
     * @template T of \PHPStan\PhpDocParser\Ast\BaseNode
     * @param T $node
     * @return T
     */
    public function createFromNode(
        \PHPStan\PhpDocParser\Ast\Node $node,
        string $docContent
    ): \PHPStan\PhpDocParser\Ast\Node {
        $this->phpDocNodeTraverser->traverseWithCallable(
            $node,
            $docContent,
            function (\PHPStan\PhpDocParser\Ast\Node $node, string $docContent) {
                foreach ($this->decoratingNodeFactories as $decoratingNodeFactory) {
                    if (! $decoratingNodeFactory->isMatch($node)) {
                        continue;
                    }

                    return $decoratingNodeFactory->create($node, $docContent);
                }

                return $node;
            }
        );

        return $node;
    }
}
