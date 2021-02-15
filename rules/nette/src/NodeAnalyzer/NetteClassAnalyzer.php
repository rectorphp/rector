<?php

declare(strict_types=1);

namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class NetteClassAnalyzer
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function isInComponent(Node $node): bool
    {
        if ($node instanceof Class_) {
            $class = $node;
        } else {
            $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        }

        if (! $class instanceof Class_) {
            return false;
        }

        if (! $this->nodeTypeResolver->isObjectType($class, 'Nette\Application\UI\Control')) {
            return false;
        }

        return ! $this->nodeTypeResolver->isObjectType($class, 'Nette\Application\UI\Presenter');
    }
}
