<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPUnit\PHPUnitDoesNotPerformAssertionTagNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPUnit\PHPUnitExpectedExceptionTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ClassMethodReflectionFactory;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use ReflectionMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://phpunit.readthedocs.io/en/7.3/annotations.html#doesnotperformassertions
 * @see https://github.com/sebastianbergmann/phpunit/issues/2484
 *
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\AddDoesNotPerformAssertionToNonAssertingTestRectorTest
 */
final class AddDoesNotPerformAssertionToNonAssertingTestRector extends AbstractRector
{
    /**
     * @var int
     */
    private const MAX_LOOKING_FOR_ASSERT_METHOD_CALL_NESTING_LEVEL = 3;

    /**
     * This should prevent segfaults while going too deep into to parsed code.
     * Without it, it might end-up with segfault
     * @var int
     */
    private $classMethodNestingLevel = 0;

    /**
     * @var bool[]
     */
    private $containsAssertCallByClassMethod = [];

    /**
     * @var FileInfoParser
     */
    private $fileInfoParser;

    /**
     * @var ClassMethodReflectionFactory
     */
    private $classMethodReflectionFactory;

    /**
     * @var ClassMethod[][]|null[][]
     */
    private $analyzedMethodsInFileName = [];

    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    public function __construct(
        TestsNodeAnalyzer $testsNodeAnalyzer,
        ClassMethodReflectionFactory $classMethodReflectionFactory,
        FileInfoParser $fileInfoParser
    ) {
        $this->fileInfoParser = $fileInfoParser;
        $this->classMethodReflectionFactory = $classMethodReflectionFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Tests without assertion will have @doesNotPerformAssertion',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function test()
    {
        $nothing = 5;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function test()
    {
        $nothing = 5;
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->classMethodNestingLevel = 0;

        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }

        $this->addDoesNotPerformAssertions($node);

        return $node;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if (! $this->testsNodeAnalyzer->isInTestClass($classMethod)) {
            return true;
        }

        if (! $this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        if ($phpDocInfo->hasByTypes(
            [PHPUnitDoesNotPerformAssertionTagNode::class, PHPUnitExpectedExceptionTagValueNode::class]
        )) {
            return true;
        }

        return $this->containsAssertCall($classMethod);
    }

    private function addDoesNotPerformAssertions(ClassMethod $classMethod): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->addPhpDocTagNode(new PHPUnitDoesNotPerformAssertionTagNode());
    }

    private function containsAssertCall(ClassMethod $classMethod): bool
    {
        ++$this->classMethodNestingLevel;

        // probably no assert method in the end
        if ($this->classMethodNestingLevel > self::MAX_LOOKING_FOR_ASSERT_METHOD_CALL_NESTING_LEVEL) {
            return false;
        }

        $cacheHash = md5($this->print($classMethod));
        if (isset($this->containsAssertCallByClassMethod[$cacheHash])) {
            return $this->containsAssertCallByClassMethod[$cacheHash];
        }

        // A. try "->assert" shallow search first for performance
        $hasDirectAssertCall = $this->hasDirectAssertCall($classMethod);
        if ($hasDirectAssertCall) {
            $this->containsAssertCallByClassMethod[$cacheHash] = $hasDirectAssertCall;
            return $hasDirectAssertCall;
        }

        // B. look for nested calls
        $hasNestedAssertCall = $this->hasNestedAssertCall($classMethod);
        $this->containsAssertCallByClassMethod[$cacheHash] = $hasNestedAssertCall;

        return $hasNestedAssertCall;
    }

    private function hasDirectAssertCall(ClassMethod $classMethod): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node): bool {
            if (! $node instanceof MethodCall && ! $node instanceof StaticCall) {
                return false;
            }

            return $this->isNames($node->name, [
                // phpunit
                '*assert',
                'assert*',
                'expectException*',
                'setExpectedException*',
            ]);
        });
    }

    private function hasNestedAssertCall(ClassMethod $classMethod): bool
    {
        $currentClassMethod = $classMethod;

        // over and over the same method :/
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use (
            $currentClassMethod
        ): bool {
            if (! $node instanceof MethodCall && ! $node instanceof StaticCall) {
                return false;
            }

            $classMethod = $this->findClassMethod($node);

            // skip circular self calls
            if ($currentClassMethod === $classMethod) {
                return false;
            }

            if ($classMethod !== null) {
                return $this->containsAssertCall($classMethod);
            }

            return false;
        });
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function findClassMethod(Node $node): ?ClassMethod
    {
        if ($node instanceof MethodCall) {
            $classMethod = $this->nodeRepository->findClassMethodByMethodCall($node);
            if ($classMethod !== null) {
                return $classMethod;
            }
        } elseif ($node instanceof StaticCall) {
            $classMethod = $this->nodeRepository->findClassMethodByStaticCall($node);
            if ($classMethod !== null) {
                return $classMethod;
            }
        }

        // in 3rd-party code
        return $this->findClassMethodByParsingReflection($node);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function findClassMethodByParsingReflection(Node $node): ?ClassMethod
    {
        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }

        if ($node instanceof MethodCall) {
            $objectType = $this->getObjectType($node->var);
        } else {
            // StaticCall
            $objectType = $this->getObjectType($node->class);
        }

        $reflectionMethod = $this->classMethodReflectionFactory->createFromPHPStanTypeAndMethodName(
            $objectType,
            $methodName
        );

        if (! $reflectionMethod instanceof ReflectionMethod) {
            return null;
        }

        $fileName = $reflectionMethod->getFileName();
        if (! $fileName) {
            return null;
        }
        if (! file_exists($fileName)) {
            return null;
        }

        return $this->findClassMethodInFile($fileName, $methodName);
    }

    private function findClassMethodInFile(string $fileName, string $methodName): ?ClassMethod
    {
        // skip already anayzed method to prevent cycling
        if (isset($this->analyzedMethodsInFileName[$fileName][$methodName])) {
            return $this->analyzedMethodsInFileName[$fileName][$methodName];
        }

        $smartFileInfo = new SmartFileInfo($fileName);
        $examinedMethodNodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate($smartFileInfo);

        /** @var ClassMethod|null $examinedClassMethod */
        $examinedClassMethod = $this->betterNodeFinder->findFirst(
            $examinedMethodNodes,
            function (Node $node) use ($methodName): bool {
                if (! $node instanceof ClassMethod) {
                    return false;
                }

                return $this->isName($node, $methodName);
            }
        );

        $this->analyzedMethodsInFileName[$fileName][$methodName] = $examinedClassMethod;

        return $examinedClassMethod;
    }
}
