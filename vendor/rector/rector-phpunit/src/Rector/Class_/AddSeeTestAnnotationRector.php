<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\Class_;

use RectorPrefix20210511\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\TestClassResolver\TestClassResolver;
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
     * @var TestClassResolver
     */
    private $testClassResolver;
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(\Rector\PHPUnit\TestClassResolver\TestClassResolver $testClassResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover)
    {
        $this->testClassResolver = $testClassResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->phpDocTagRemover = $phpDocTagRemover;
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
        $testCaseClassName = $this->testClassResolver->resolveFromClass($node);
        if ($testCaseClassName === null) {
            return null;
        }
        if ($this->shouldSkipClass($node, $testCaseClassName)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($this->hasAlreadySeeAnnotation($phpDocInfo, $testCaseClassName)) {
            return null;
        }
        $this->removeNonExistingClassSeeAnnotation($phpDocInfo);
        $newSeeTagNode = $this->createSeePhpDocTagNode($testCaseClassName);
        $phpDocInfo->addPhpDocTagNode($newSeeTagNode);
        return $node;
    }
    private function shouldSkipClass(\PhpParser\Node\Stmt\Class_ $class, string $testCaseClassName) : bool
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
            if ($seeTagClass === $testCaseClassName) {
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
        if (!\RectorPrefix20210511\Nette\Utils\Strings::startsWith($possibleClassName, '\\')) {
            return \false;
        }
        return \RectorPrefix20210511\Nette\Utils\Strings::endsWith($possibleClassName, 'Test');
    }
}
