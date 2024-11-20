<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony34\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony34\Rector\ClassMethod\RemoveServiceFromSensioRouteRector\RemoveServiceFromSensioRouteRectorTest
 */
final class RemoveServiceFromSensioRouteRector extends AbstractRector
{
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove service from Sensio @Route', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Class_::class];
    }
    /**
     * @param ClassMethod|Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Route');
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $doctrineAnnotationTagValueNode->removeValue('service');
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
}
