<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\NodeFactory;

use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;

final class SetUpFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function createParentStaticCall(): Expression
    {
        $parentSetupStaticCall = $this->nodeFactory->createStaticCall('parent', MethodName::SET_UP);
        return new Expression($parentSetupStaticCall);
    }
}
