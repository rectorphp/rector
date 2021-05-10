<?php

declare (strict_types=1);
namespace Rector\RemovingStatic\NodeFactory;

use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
final class SetUpFactory
{
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function createParentStaticCall() : \PhpParser\Node\Stmt\Expression
    {
        $parentSetupStaticCall = $this->nodeFactory->createStaticCall('parent', \Rector\Core\ValueObject\MethodName::SET_UP);
        return new \PhpParser\Node\Stmt\Expression($parentSetupStaticCall);
    }
}
