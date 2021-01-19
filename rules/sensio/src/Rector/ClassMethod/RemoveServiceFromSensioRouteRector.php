<?php

declare(strict_types=1);

namespace Rector\Sensio\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioRouteTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html#route-annotation
 *
 * @see \Rector\Sensio\Tests\Rector\ClassMethod\RemoveServiceFromSensioRouteRector\RemoveServiceFromSensioRouteRectorTest
 */
final class RemoveServiceFromSensioRouteRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove service from Sensio @Route', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

final class SomeClass
{
    /**
     * @Route(service="some_service")
     */
    public function run()
    {
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

final class SomeClass
{
    /**
     * @Route()
     */
    public function run()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Class_::class];
    }

    /**
     * @param ClassMethod|Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $sensioRouteTagValueNode = $phpDocInfo->getByType(SensioRouteTagValueNode::class);
        if (! $sensioRouteTagValueNode instanceof SensioRouteTagValueNode) {
            return null;
        }

        $sensioRouteTagValueNode->removeService();
        $phpDocInfo->markAsChanged();

        return $node;
    }
}
