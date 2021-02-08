<?php

declare(strict_types=1);

namespace Rector\Privatization\NodeFinder;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ParentClassConstantNodeFinder
{
    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    public function find(string $class, string $constant): ?ClassConst
    {
        $classNode = $this->nodeRepository->findClass($class);
        if (! $classNode instanceof Class_) {
            return null;
        }

        /** @var string|null $parentClassName */
        $parentClassName = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return null;
        }

        return $this->nodeRepository->findClassConstant($parentClassName, $constant);
    }
}
