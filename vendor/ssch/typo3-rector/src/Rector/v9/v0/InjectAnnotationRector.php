<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
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
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer
     */
    private $docBlockTagReplacer;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover, \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer $docBlockTagReplacer)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
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
            /** @var string $variableName */
            $variableName = $this->getName($property);
            $paramBuilder = new \RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder($variableName);
            $varType = $propertyPhpDocInfo->getVarType();
            if (!$varType instanceof \PHPStan\Type\ObjectType) {
                continue;
            }
            // Remove the old annotation and use setterInjection instead
            $this->phpDocTagRemover->removeByName($propertyPhpDocInfo, self::OLD_ANNOTATION);
            if ($varType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
                $paramBuilder->setType(new \PhpParser\Node\Name\FullyQualified($varType->getClassName()));
            } elseif ($varType instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
                $paramBuilder->setType($varType->getShortName());
            }
            $param = $paramBuilder->getNode();
            $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $variableName);
            $assign = new \PhpParser\Node\Expr\Assign($propertyFetch, new \PhpParser\Node\Expr\Variable($variableName));
            // Add new line and then the method
            $injectMethods[] = new \PhpParser\Node\Stmt\Nop();
            $methodAlreadyExists = $node->getMethod($this->createInjectMethodName($variableName));
            if (!$methodAlreadyExists instanceof \PhpParser\Node\Stmt\ClassMethod) {
                $injectMethods[] = $this->createInjectClassMethod($variableName, $param, $assign);
            }
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
    private function createInjectClassMethod(string $variableName, \PhpParser\Node\Param $param, \PhpParser\Node\Expr\Assign $assign) : \PhpParser\Node\Stmt\ClassMethod
    {
        $injectMethodName = $this->createInjectMethodName($variableName);
        $injectMethodBuilder = new \RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder($injectMethodName);
        $injectMethodBuilder->makePublic();
        $injectMethodBuilder->addParam($param);
        $injectMethodBuilder->setReturnType('void');
        $injectMethodBuilder->addStmt($assign);
        return $injectMethodBuilder->getNode();
    }
    private function createInjectMethodName(string $variableName) : string
    {
        return 'inject' . \ucfirst($variableName);
    }
}
