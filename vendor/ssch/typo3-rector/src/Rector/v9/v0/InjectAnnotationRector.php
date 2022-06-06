<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer;
use RectorPrefix20220606\Ssch\TYPO3Rector\NodeFactory\InjectMethodFactory;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Feature-82869-ReplaceInjectWithTYPO3CMSExtbaseAnnotationInject.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\InjectAnnotationRector\InjectAnnotationRectorTest
 */
final class InjectAnnotationRector extends AbstractRector
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
    public function __construct(InjectMethodFactory $injectMethodFactory, DocBlockTagReplacer $docBlockTagReplacer)
    {
        $this->injectMethodFactory = $injectMethodFactory;
        $this->docBlockTagReplacer = $docBlockTagReplacer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns properties with `@inject` to setter injection', [new CodeSample(<<<'CODE_SAMPLE'
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
