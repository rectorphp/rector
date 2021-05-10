<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
final class CallablePhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @var callable
     */
    private $callable;
    /**
     * @var string|null
     */
    private $docContent;
    public function __construct(callable $callable, ?string $docContent = null)
    {
        $this->callable = $callable;
        $this->docContent = $docContent;
    }
    public function enterNode(Node $node) : ?Node
    {
        $callable = $this->callable;
        return $callable($node, $this->docContent);
    }
}
