<?php

declare(strict_types=1);

namespace Rector\Sensio\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Use_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio\SensioRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://medium.com/@nebkam/symfony-deprecated-route-and-method-annotations-4d5e1d34556a
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html#method-annotation
 *
 * @see \Rector\Sensio\Tests\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector\ReplaceSensioRouteAnnotationWithSymfonyRectorTest
 */
final class ReplaceSensioRouteAnnotationWithSymfonyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace Sensio @Route annotation with Symfony one', [
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
        return [ClassMethod::class, Class_::class, Use_::class];
    }

    /**
     * @param ClassMethod|Class_|Use_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Use_) {
            return $this->refactorUse($node);
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        if ($phpDocInfo->hasByType(SymfonyRouteTagValueNode::class)) {
            return null;
        }

        /** @var SensioRouteTagValueNode|null $sensioRouteTagValueNode */
        $sensioRouteTagValueNode = $phpDocInfo->getByType(SensioRouteTagValueNode::class);
        if ($sensioRouteTagValueNode === null) {
            return null;
        }

        /** @var SensioRouteTagValueNode $sensioRouteTagValueNode */
        $sensioRouteTagValueNode = $phpDocInfo->getByType(SensioRouteTagValueNode::class);
        $phpDocInfo->removeTagValueNodeFromNode($sensioRouteTagValueNode);

        // unset service, that is deprecated
        $items = $sensioRouteTagValueNode->getItems();
        $symfonyRouteTagValueNode = new SymfonyRouteTagValueNode($items);
        $symfonyRouteTagValueNode->mimicTagValueNodeConfiguration($sensioRouteTagValueNode);

        $phpDocInfo->addTagValueNodeWithShortName($symfonyRouteTagValueNode);

        return $node;
    }

    private function refactorUse(Use_ $use): ?Use_
    {
        if ($use->type !== Use_::TYPE_NORMAL) {
            return null;
        }

        foreach ($use->uses as $useUse) {
            if (! $this->isName($useUse->name, 'Sensio\Bundle\FrameworkExtraBundle\Configuration\Route')) {
                continue;
            }

            $useUse->name = new Name('Symfony\Component\Routing\Annotation\Route');
        }

        return $use;
    }
}
