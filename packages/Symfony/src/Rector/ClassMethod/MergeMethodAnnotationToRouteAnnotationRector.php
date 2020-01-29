<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioMethodTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html#method-annotation
 * @see https://stackoverflow.com/questions/51171934/how-to-fix-symfony-3-4-route-and-method-deprecation
 *
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector\MergeMethodAnnotationToRouteAnnotationRectorTest
 */
final class MergeMethodAnnotationToRouteAnnotationRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Merge removed @Method annotation to @Route one', [
            new CodeSample(
                <<<'PHP'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/show/{id}")
     * @Method({"GET", "HEAD"})
     */
    public function show($id)
    {
    }
}
PHP
                ,
                <<<'PHP'
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/show/{id}", methods={"GET","HEAD"})
     */
    public function show($id)
    {
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return null;
        }

        if (! $this->isObjectType($classNode, '*Controller')) {
            return null;
        }

        if (! $node->isPublic()) {
            return null;
        }

        $phpDocInfo = $this->getPhpDocInfo($node);
        if ($phpDocInfo === null) {
            return null;
        }

        $symfonyMethodPhpDocTagValueNode = $phpDocInfo->getByType(SensioMethodTagValueNode::class);
        if ($symfonyMethodPhpDocTagValueNode === null) {
            return null;
        }

        $methods = $symfonyMethodPhpDocTagValueNode->getMethods();

        /** @var SymfonyRouteTagValueNode $symfonyRoutePhpDocTagValueNode */
        $symfonyRoutePhpDocTagValueNode = $phpDocInfo->getByType(SymfonyRouteTagValueNode::class);
        $symfonyRoutePhpDocTagValueNode->changeMethods($methods);

        $phpDocInfo->removeTagValueNodeFromNode($symfonyMethodPhpDocTagValueNode);

        return $node;
    }
}
