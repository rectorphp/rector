<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\Naming\TestClassNameResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\AddSeeTestAnnotationRector\AddSeeTestAnnotationRectorTest
 */
final class AddSeeTestAnnotationRector extends AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\PHPUnit\Naming\TestClassNameResolver
     */
    private $testClassNameResolver;
    /**
     * @var string
     */
    private const SEE = 'see';
    public function __construct(ReflectionProvider $reflectionProvider, PhpDocTagRemover $phpDocTagRemover, TestClassNameResolver $testClassNameResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->testClassNameResolver = $testClassNameResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add @see annotation test of the class for faster jump to test. Make it FQN, so it stays in the annotation, not in the PHP source code.', [new CodeSample(<<<'CODE_SAMPLE'
class SomeService
{
}

use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @see \SomeServiceTest
 */
class SomeService
{
}

use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
}
CODE_SAMPLE
)]);
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
        $className = $this->getName($node);
        if ($className === null) {
            return null;
        }
        $possibleTestClassNames = $this->testClassNameResolver->resolve($className);
        $matchingTestClassName = $this->matchExistingClassName($possibleTestClassNames);
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->removeNonExistingClassSeeAnnotation($phpDocInfo);
        if ($matchingTestClassName === null) {
            return null;
        }
        if ($this->hasAlreadySeeAnnotation($phpDocInfo, $matchingTestClassName)) {
            return null;
        }
        $phpDocTagNode = $this->createSeePhpDocTagNode($matchingTestClassName);
        $phpDocInfo->addPhpDocTagNode($phpDocTagNode);
        return $node;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        // we are in the test case
        if ($this->isName($class, '*Test')) {
            return \true;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        /** @var PhpDocTagNode[] $seePhpDocTagNodes */
        $seePhpDocTagNodes = $phpDocInfo->getTagsByName(self::SEE);
        // is the @see annotation already added
        foreach ($seePhpDocTagNodes as $seePhpDocTagNode) {
            if (!$seePhpDocTagNode->value instanceof GenericTagValueNode) {
                continue;
            }
            /** @var GenericTagValueNode $genericTagValueNode */
            $genericTagValueNode = $seePhpDocTagNode->value;
            $seeTagClass = \ltrim($genericTagValueNode->value, '\\');
            if ($this->reflectionProvider->hasClass($seeTagClass)) {
                return \true;
            }
        }
        return \false;
    }
    private function createSeePhpDocTagNode(string $className) : PhpDocTagNode
    {
        return new PhpDocTagNode('@see', new GenericTagValueNode('\\' . $className));
    }
    private function hasAlreadySeeAnnotation(PhpDocInfo $phpDocInfo, string $testCaseClassName) : bool
    {
        /** @var PhpDocTagNode[] $seePhpDocTagNodes */
        $seePhpDocTagNodes = $phpDocInfo->getTagsByName(self::SEE);
        foreach ($seePhpDocTagNodes as $seePhpDocTagNode) {
            if (!$seePhpDocTagNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $possibleClassName = $seePhpDocTagNode->value->value;
            // annotation already exists
            if ($possibleClassName === '\\' . $testCaseClassName) {
                return \true;
            }
        }
        return \false;
    }
    private function removeNonExistingClassSeeAnnotation(PhpDocInfo $phpDocInfo) : void
    {
        /** @var PhpDocTagNode[] $seePhpDocTagNodes */
        $seePhpDocTagNodes = $phpDocInfo->getTagsByName(self::SEE);
        foreach ($seePhpDocTagNodes as $seePhpDocTagNode) {
            if (!$seePhpDocTagNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $possibleClassName = $seePhpDocTagNode->value->value;
            if (!$this->isSeeTestCaseClass($possibleClassName)) {
                continue;
            }
            if ($this->reflectionProvider->hasClass($possibleClassName)) {
                continue;
            }
            // remove old annotation
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $seePhpDocTagNode);
        }
    }
    private function isSeeTestCaseClass(string $possibleClassName) : bool
    {
        if (\strncmp($possibleClassName, '\\', \strlen('\\')) !== 0) {
            return \false;
        }
        return \substr_compare($possibleClassName, 'Test', -\strlen('Test')) === 0;
    }
    /**
     * @param string[] $classNames
     */
    private function matchExistingClassName(array $classNames) : ?string
    {
        foreach ($classNames as $className) {
            if (!$this->reflectionProvider->hasClass($className)) {
                continue;
            }
            return $className;
        }
        return null;
    }
}
