<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\TestClassResolver\TestClassResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PHPStan\Reflection\ReflectionProvider;

/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\AddSeeTestAnnotationRectorTest
 */
final class AddSeeTestAnnotationRector extends AbstractRector
{
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

    public function __construct(
        TestClassResolver $testClassResolver,
        ReflectionProvider $reflectionProvider,
        PhpDocTagRemover $phpDocTagRemover
    ) {
        $this->testClassResolver = $testClassResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add @see annotation test of the class for faster jump to test. Make it FQN, so it stays in the annotation, not in the PHP source code.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeService
{
}

use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
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

    private function shouldSkipClass(Class_ $class, string $testCaseClassName): bool
    {
        // we are in the test case
        if ($this->isName($class, '*Test')) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        $seeTags = $phpDocInfo->getTagsByName('see');

        // is the @see annotation already added
        foreach ($seeTags as $seeTag) {
            if (! $seeTag->value instanceof GenericTagValueNode) {
                continue;
            }

            /** @var GenericTagValueNode $genericTagValueNode */
            $genericTagValueNode = $seeTag->value;

            $seeTagClass = ltrim($genericTagValueNode->value, '\\');
            if ($seeTagClass === $testCaseClassName) {
                return true;
            }
        }

        return false;
    }

    private function createSeePhpDocTagNode(string $className): PhpDocTagNode
    {
        return new AttributeAwarePhpDocTagNode('@see', new AttributeAwareGenericTagValueNode('\\' . $className));
    }

    private function hasAlreadySeeAnnotation(PhpDocInfo $phpDocInfo, string $testCaseClassName): bool
    {
        $seePhpDocTagNodes = $phpDocInfo->getTagsByName('see');

        foreach ($seePhpDocTagNodes as $seePhpDocTagNode) {
            if (! $seePhpDocTagNode->value instanceof GenericTagValueNode) {
                continue;
            }

            $possibleClassName = $seePhpDocTagNode->value->value;

            // annotation already exists
            if ($possibleClassName === '\\' . $testCaseClassName) {
                return true;
            }
        }

        return false;
    }

    private function removeNonExistingClassSeeAnnotation(PhpDocInfo $phpDocInfo): void
    {
        $seePhpDocTagNodes = $phpDocInfo->getTagsByName('see');

        foreach ($seePhpDocTagNodes as $seePhpDocTagNode) {
            if (! $seePhpDocTagNode->value instanceof GenericTagValueNode) {
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

    private function isSeeTestCaseClass(string $possibleClassName): bool
    {
        if (! Strings::startsWith($possibleClassName, '\\')) {
            return false;
        }

        return Strings::endsWith($possibleClassName, 'Test');
    }
}
