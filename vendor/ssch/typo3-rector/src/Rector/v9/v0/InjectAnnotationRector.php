<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer;
use Ssch\TYPO3Rector\NodeFactory\InjectMethodFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Feature-82869-ReplaceInjectWithTYPO3CMSExtbaseAnnotationInject.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\InjectAnnotationRector\InjectAnnotationRectorTest
 */
final class InjectAnnotationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const OLD_ANNOTATION = 'inject';
    /**
     * @var string
     */
    private const NEW_ANNOTATION = 'TYPO3\\CMS\\Extbase\\Annotation\\Inject';
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\NodeFactory\InjectMethodFactory
     */
    private $injectMethodFactory;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer
     */
    private $docBlockTagReplacer;
    public function __construct(\Ssch\TYPO3Rector\NodeFactory\InjectMethodFactory $injectMethodFactory, \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer $docBlockTagReplacer)
    {
        $this->injectMethodFactory = $injectMethodFactory;
        $this->docBlockTagReplacer = $docBlockTagReplacer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $injectMethods = [];
        $properties = $node->getProperties();
        foreach ($properties as $property) {
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if (!$propertyPhpDocInfo->hasByName(self::OLD_ANNOTATION)) {
                continue;
            }
            // If the property is public, then change the annotation name
            if ($property->isPublic()) {
                $this->docBlockTagReplacer->replaceTagByAnother($propertyPhpDocInfo, self::OLD_ANNOTATION, self::NEW_ANNOTATION);
                continue;
            }
            $injectMethods = \array_merge($injectMethods, $this->injectMethodFactory->createInjectMethodStatements($node, $property, self::OLD_ANNOTATION));
        }
        $node->stmts = \array_merge($node->stmts, $injectMethods);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns properties with `@inject` to setter injection', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
/**
 * @var SomeService
 * @inject
 */
private $someService;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @var SomeService
 */
private $someService;

public function injectSomeService(SomeService $someService)
{
    $this->someService = $someService;
}

CODE_SAMPLE
)]);
    }
}
