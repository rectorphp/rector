<?php

declare (strict_types=1);
namespace Rector\Php80\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFinder\DoctrineAnnotationMatcher;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute;
use RectorPrefix20210701\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
use RectorPrefix20210701\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class AnnotationToAttributePhpDocNodeVisitor extends \RectorPrefix20210701\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor
{
    /**
     * @var AnnotationToAttribute[]
     */
    private $annotationsToAttributes = [];
    /**
     * @var DoctrineTagAndAnnotationToAttribute[]
     */
    private $doctrineTagAndAnnotationToAttributes = [];
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocNodeFinder\DoctrineAnnotationMatcher
     */
    private $doctrineAnnotationMatcher;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocNodeFinder\DoctrineAnnotationMatcher $doctrineAnnotationMatcher)
    {
        $this->doctrineAnnotationMatcher = $doctrineAnnotationMatcher;
    }
    /**
     * @param AnnotationToAttribute[] $annotationsToAttributes
     */
    public function configureAnnotationsToAttributes(array $annotationsToAttributes) : void
    {
        $this->annotationsToAttributes = $annotationsToAttributes;
    }
    public function beforeTraverse(\PHPStan\PhpDocParser\Ast\Node $node) : void
    {
        $this->doctrineTagAndAnnotationToAttributes = [];
        if ($this->annotationsToAttributes !== []) {
            return;
        }
        throw new \Rector\Core\Exception\ShouldNotHappenException('Configure annotations to attributes via "configureAnnotationsToAttributes()" method first');
    }
    /**
     * @return int|\PHPStan\PhpDocParser\Ast\Node|null
     */
    public function enterNode(\PHPStan\PhpDocParser\Ast\Node $node)
    {
        if (!$node instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return $node;
        }
        foreach ($this->annotationsToAttributes as $annotationToAttribute) {
            if (!$this->doctrineAnnotationMatcher->matches($node, $annotationToAttribute->getTag())) {
                continue;
            }
            $this->doctrineTagAndAnnotationToAttributes[] = new \Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute($node, $annotationToAttribute);
            $parentNode = $node->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::PARENT);
            if ($parentNode instanceof \PHPStan\PhpDocParser\Ast\Node) {
                // invoke re-print
                $parentNode->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::ORIG_NODE, null);
            }
            // remove the original doctrine annotation, it becomes an attribute
            return \RectorPrefix20210701\Symplify\SimplePhpDocParser\PhpDocNodeTraverser::NODE_REMOVE;
        }
        return $node;
    }
    /**
     * @return DoctrineTagAndAnnotationToAttribute[]
     */
    public function provideFound() : array
    {
        return $this->doctrineTagAndAnnotationToAttributes;
    }
}
