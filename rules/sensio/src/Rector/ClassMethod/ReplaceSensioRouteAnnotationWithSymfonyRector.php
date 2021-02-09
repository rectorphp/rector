<?php

declare(strict_types=1);

namespace Rector\Sensio\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Symfony\SymfonyRouteTagValueNodeFactory;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://medium.com/@nebkam/symfony-deprecated-route-and-method-annotations-4d5e1d34556a
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html#method-annotation
 *
 * @see \Rector\Sensio\Tests\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector\ReplaceSensioRouteAnnotationWithSymfonyRectorTest
 */
final class ReplaceSensioRouteAnnotationWithSymfonyRector extends AbstractRector
{
    /**
     * @var SymfonyRouteTagValueNodeFactory
     */
    private $symfonyRouteTagValueNodeFactory;

    public function __construct(SymfonyRouteTagValueNodeFactory $symfonyRouteTagValueNodeFactory)
    {
        $this->symfonyRouteTagValueNodeFactory = $symfonyRouteTagValueNodeFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace Sensio @Route annotation with Symfony one',
            [
                new CodeSample(
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
,
                    <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

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
        if ($phpDocInfo->hasByType(SymfonyRouteTagValueNode::class)) {
            return null;
        }

        $sensioRouteTagValueNode = $phpDocInfo->getByType(SensioRouteTagValueNode::class);
        if (! $sensioRouteTagValueNode instanceof SensioRouteTagValueNode) {
            return null;
        }

        $phpDocInfo->removeByType(SensioRouteTagValueNode::class);

        // unset service, that is deprecated
        $items = $sensioRouteTagValueNode->getItems();
        $symfonyRouteTagValueNode = $this->symfonyRouteTagValueNodeFactory->createFromItems($items);
        $symfonyRouteTagValueNode->mimicTagValueNodeConfiguration($sensioRouteTagValueNode);

        $phpDocInfo->addTagValueNode($symfonyRouteTagValueNode);

        return $node;
    }
}
