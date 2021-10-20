<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\Class_;

use RectorPrefix20211020\Nette\Utils\Strings;
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
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\AddSeeTestAnnotationRectorTest
 */
final class AddSeeTestAnnotationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const SEE = 'see';
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @var \Rector\PHPUnit\Naming\TestClassNameResolver
     */
    private $testClassNameResolver;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover, \Rector\PHPUnit\Naming\TestClassNameResolver $testClassNameResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->testClassNameResolver = $testClassNameResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add @see annotation test of the class for faster jump to test. Make it FQN, so it stays in the annotation, not in the PHP source code.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
        if ($matchingTestClassName !== null) {
            if ($this->hasAlreadySeeAnnotation($phpDocInfo, $matchingTestClassName)) {
                return null;
            }
            $newSeeTagNode = $this->createSeePhpDocTagNode($matchingTestClassName);
            $phpDocInfo->addPhpDocTagNode($newSeeTagNode);
        }
        return $node;
    }
    private function shouldSkipClass(\PhpParser\Node\Stmt\Class_ $class) : bool
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
            if (!$seePhpDocTagNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode) {
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
    private function createSeePhpDocTagNode(string $className) : \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode
    {
        return new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode('@see', new \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode('\\' . $className));
    }
    private function hasAlreadySeeAnnotation(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, string $testCaseClassName) : bool
    {
        /** @var PhpDocTagNode[] $seePhpDocTagNodes */
        $seePhpDocTagNodes = $phpDocInfo->getTagsByName(self::SEE);
        foreach ($seePhpDocTagNodes as $seePhpDocTagNode) {
            if (!$seePhpDocTagNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode) {
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
    private function removeNonExistingClassSeeAnnotation(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : void
    {
        $seePhpDocTagNodes = $phpDocInfo->getTagsByName(self::SEE);
        /** @var PhpDocTagNode[] $seePhpDocTagNodes */
        foreach ($seePhpDocTagNodes as $seePhpDocTagNode) {
            if (!$seePhpDocTagNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode) {
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
        if (!\RectorPrefix20211020\Nette\Utils\Strings::startsWith($possibleClassName, '\\')) {
            return \false;
        }
        return \RectorPrefix20211020\Nette\Utils\Strings::endsWith($possibleClassName, 'Test');
    }
    /**
     * @param string[] $classNames
     */
    private function matchExistingClassName(array $classNames) : ?string
    {
        foreach ($classNames as $possibleTestClassName) {
            if (!$this->reflectionProvider->hasClass($possibleTestClassName)) {
                continue;
            }
            return $possibleTestClassName;
        }
        return null;
    }
}
