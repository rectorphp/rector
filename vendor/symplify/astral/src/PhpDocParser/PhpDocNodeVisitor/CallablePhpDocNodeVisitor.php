<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Astral\PhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
final class CallablePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @var callable(Node, string|null): (int|null|Node)
     */
    private $callable;
    /**
     * @var string|null
     */
    private $docContent;
    /**
     * @param callable(Node $callable, string|null $docContent): (int|null|Node) $callable
     */
    public function __construct(callable $callable, ?string $docContent)
    {
        $this->docContent = $docContent;
        $this->callable = $callable;
    }
    /**
     * @return int|\PHPStan\PhpDocParser\Ast\Node|null
     */
    public function enterNode(Node $node)
    {
        $callable = $this->callable;
        return $callable($node, $this->docContent);
    }
}
