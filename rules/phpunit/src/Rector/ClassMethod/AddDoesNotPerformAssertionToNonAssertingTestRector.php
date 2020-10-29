<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Reflection\ClassMethodReflectionFactory;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://phpunit.readthedocs.io/en/7.3/annotations.html#doesnotperformassertions
 * @see https://github.com/sebastianbergmann/phpunit/issues/2484
 *
 * @see \Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\AddDoesNotPerformAssertionToNonAssertingTestRectorTest
 */
final class AddDoesNotPerformAssertionToNonAssertingTestRector extends AbstractPHPUnitRector
{
    /**
     * @var int
     */
    private const MAX_LOOKING_FOR_ASSERT_METHOD_CALL_NESTING_LEVEL = 3;

    /**
     * @var string
     * @see https://regex101.com/r/pn4Kma/1
     */
    private const DOES_NOT_PERFORM_ASSERTION_REGEX = '#@(doesNotPerformAssertion|expectedException\b)#';

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

    public function __construct(
        ClassMethodReflectionFactory $classMethodReflectionFactory,
        DocBlockManipulator $docBlockManipulator,
        FileInfoParser $fileInfoParser
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->fileInfoParser = $fileInfoParser;
        $this->classMethodReflectionFactory = $classMethodReflectionFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Tests without assertion will have @doesNotPerformAssertion', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $nothing = 5;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
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
        if (! $this->isInTestClass($classMethod)) {
            return true;
        }

        if (! $this->isTestClassMethod($classMethod)) {
            return true;
        }

        if ($classMethod->getDocComment() !== null) {
            $doc = $classMethod->getDocComment();
            if (Strings::match($doc->getText(), self::DOES_NOT_PERFORM_ASSERTION_REGEX)) {
                return true;
            }
        }

        return $this->containsAssertCall($classMethod);
    }

    private function addDoesNotPerformAssertions(ClassMethod $classMethod): void
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->addBareTag('@doesNotPerformAssertions');
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

        if ($reflectionMethod === null) {
            return null;
        }

        $fileName = $reflectionMethod->getFileName();
        if (! $fileName || ! file_exists($fileName)) {
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
