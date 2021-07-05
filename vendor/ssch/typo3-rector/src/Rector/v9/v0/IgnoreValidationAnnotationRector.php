<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\NodeFactory\ImportExtbaseAnnotationIfMissingFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Feature-83094-ReplaceIgnorevalidationWithTYPO3CMSExtbaseAnnotationIgnoreValidation.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\IgnoreValidationAnnotationRector\IgnoreValidationAnnotationRectorTest
 */
final class IgnoreValidationAnnotationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const OLD_ANNOTATION = 'ignorevalidation';
    /**
     * @var string
     */
    private const VERY_OLD_ANNOTATION = 'dontvalidate';
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @var \Ssch\TYPO3Rector\NodeFactory\ImportExtbaseAnnotationIfMissingFactory
     */
    private $importExtbaseAnnotationIfMissingFactory;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover, \Ssch\TYPO3Rector\NodeFactory\ImportExtbaseAnnotationIfMissingFactory $importExtbaseAnnotationIfMissingFactory)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->importExtbaseAnnotationIfMissingFactory = $importExtbaseAnnotationIfMissingFactory;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if (!$phpDocInfo->hasByNames([self::OLD_ANNOTATION, self::VERY_OLD_ANNOTATION])) {
            return null;
        }
        $this->importExtbaseAnnotationIfMissingFactory->addExtbaseAliasAnnotationIfMissing($node);
        if ($phpDocInfo->hasByName(self::OLD_ANNOTATION)) {
            return $this->refactorValidation(self::OLD_ANNOTATION, $phpDocInfo, $node);
        }
        return $this->refactorValidation(self::VERY_OLD_ANNOTATION, $phpDocInfo, $node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns properties with `@ignorevalidation` to properties with `@TYPO3\\CMS\\Extbase\\Annotation\\IgnoreValidation`', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
/**
 * @ignorevalidation $param
 */
public function method($param)
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Annotation as Extbase;
/**
 * @Extbase\IgnoreValidation("param")
 */
public function method($param)
{
}
CODE_SAMPLE
)]);
    }
    private function refactorValidation(string $oldAnnotation, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Stmt\ClassMethod $node) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $tagNode = $phpDocInfo->getTagsByName($oldAnnotation)[0];
        if (!\property_exists($tagNode, 'value')) {
            return null;
        }
        $tagName = '@Extbase\\IgnoreValidation("' . \ltrim((string) $tagNode->value, '$') . '")';
        $tag = '@' . \ltrim($tagName, '@');
        $phpDocTagNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode($tag, new \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode(''));
        $phpDocInfo->addPhpDocTagNode($phpDocTagNode);
        $this->phpDocTagRemover->removeByName($phpDocInfo, $oldAnnotation);
        return $node;
    }
}
