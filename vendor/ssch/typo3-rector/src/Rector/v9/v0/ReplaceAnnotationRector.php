<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\NodeFactory\ImportExtbaseAnnotationIfMissingFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Feature-83092-ReplaceTransientWithTYPO3CMSExtbaseAnnotationORMTransient.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\ReplaceAnnotationRector\ReplaceAnnotationRectorTest
 */
final class ReplaceAnnotationRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const OLD_TO_NEW_ANNOTATIONS = 'old_to_new_annotations';
    /**
     * @var array<string, string>
     */
    private $oldToNewAnnotations = [];
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
        return [\PhpParser\Node\Stmt\Property::class, \PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param Property|ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $annotationChanged = \false;
        foreach ($this->oldToNewAnnotations as $oldAnnotation => $newAnnotation) {
            if (!$phpDocInfo->hasByName($oldAnnotation)) {
                continue;
            }
            $this->phpDocTagRemover->removeByName($phpDocInfo, $oldAnnotation);
            $tag = $this->prepareNewAnnotation($newAnnotation);
            $phpDocTagNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode($tag, new \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode(''));
            $phpDocInfo->addPhpDocTagNode($phpDocTagNode);
            $annotationChanged = \true;
        }
        if (!$annotationChanged) {
            return null;
        }
        $this->importExtbaseAnnotationIfMissingFactory->addExtbaseAliasAnnotationIfMissing($node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace old annotation by new one', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
/**
 * @transient
 */
private $someProperty;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Annotation as Extbase;
/**
 * @Extbase\ORM\Transient
 */
private $someProperty;

CODE_SAMPLE
, [self::OLD_TO_NEW_ANNOTATIONS => ['transient' => 'TYPO3\\CMS\\Extbase\\Annotation\\ORM\\Transient']])]);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->oldToNewAnnotations = $configuration[self::OLD_TO_NEW_ANNOTATIONS] ?? [];
    }
    private function prepareNewAnnotation(string $newAnnotation) : string
    {
        $newAnnotation = '@' . \ltrim($newAnnotation, '@');
        if (\strncmp($newAnnotation, '@TYPO3\\CMS\\Extbase\\Annotation', \strlen('@TYPO3\\CMS\\Extbase\\Annotation')) === 0) {
            $newAnnotation = \str_replace('TYPO3\\CMS\\Extbase\\Annotation', 'Extbase', $newAnnotation);
        }
        return '@' . \ltrim($newAnnotation, '@');
    }
}
