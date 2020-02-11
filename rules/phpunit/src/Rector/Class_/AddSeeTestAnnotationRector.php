<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use Nette\Loaders\RobotLoader;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\Composer\ComposerAutoloadedDirectoryProvider;

/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\AddSeeTestAnnotationRectorTest
 */
final class AddSeeTestAnnotationRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $phpUnitTestCaseClasses = [];

    /**
     * @var ComposerAutoloadedDirectoryProvider
     */
    private $composerAutoloadedDirectoryProvider;

    public function __construct(ComposerAutoloadedDirectoryProvider $composerAutoloadedDirectoryProvider)
    {
        $this->composerAutoloadedDirectoryProvider = $composerAutoloadedDirectoryProvider;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Add @see annotation test of the class for faster jump to test. Make it FQN, so it stays in the annotation, not in the PHP source code.',
            [
                new CodeSample(
                    <<<'PHP'
class SomeService
{
}

use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
}
PHP
                    ,
                    <<<'PHP'
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
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
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
        $testCaseClassName = $this->resolveTestCaseClassName($node);
        if ($testCaseClassName === null) {
            return null;
        }

        if ($this->shouldSkipClass($node, $testCaseClassName)) {
            return null;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $seeTagNode = $this->createSeePhpDocTagNode($testCaseClassName);
        $phpDocInfo->addPhpDocTagNode($seeTagNode);

        return $node;
    }

    private function shouldSkipClass(Class_ $class, string $testCaseClassName): bool
    {
        // we are in the test case
        if ($this->isName($class, '*Test')) {
            return true;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $class->getAttribute(AttributeKey::PHP_DOC_INFO);

        $seeTags = $phpDocInfo->getTagsByName('see');

        // is the @see annotation already added
        foreach ($seeTags as $seeTag) {
            if (! $seeTag->value instanceof GenericTagValueNode) {
                continue;
            }

            $seeTagClass = ltrim($seeTag->value->value, '\\');
            if ($seeTagClass === $testCaseClassName) {
                return true;
            }
        }

        return false;
    }

    private function resolveTestCaseClassName(Class_ $class): ?string
    {
        if ($this->isAnonymousClass($class)) {
            return null;
        }

        $className = $this->getName($class);
        if ($className === null) {
            return null;
        }

        // fallback for unit tests that only have extra "Test" suffix
        if (class_exists($className . 'Test')) {
            return $className . 'Test';
        }

        $shortClassName = Strings::after($className, '\\', -1);
        $testShortClassName = $shortClassName . 'Test';

        $phpUnitTestCaseClasses = $this->getPhpUnitTestCaseClasses();
        foreach ($phpUnitTestCaseClasses as $declaredClass) {
            if (Strings::endsWith($declaredClass, '\\' . $testShortClassName)) {
                return $declaredClass;
            }
        }

        return null;
    }

    private function createSeePhpDocTagNode(string $className): PhpDocTagNode
    {
        return new AttributeAwarePhpDocTagNode('@see', new AttributeAwareGenericTagValueNode('\\' . $className));
    }

    /**
     * @return string[]
     */
    private function getPhpUnitTestCaseClasses(): array
    {
        if ($this->phpUnitTestCaseClasses !== []) {
            return $this->phpUnitTestCaseClasses;
        }

        $robotLoader = $this->createRobotLoadForDirectories();
        $robotLoader->rebuild();

        $this->phpUnitTestCaseClasses = array_keys($robotLoader->getIndexedClasses());

        return $this->phpUnitTestCaseClasses;
    }

    private function createRobotLoadForDirectories(): RobotLoader
    {
        $robotLoader = new RobotLoader();
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/tests_add_see_rector_tests');

        $directories = $this->composerAutoloadedDirectoryProvider->provide();
        foreach ($directories as $directory) {
            $robotLoader->addDirectory($directory);
        }

        $robotLoader->acceptFiles = ['*Test.php'];
        $robotLoader->ignoreDirs[] = '*Expected*';
        $robotLoader->ignoreDirs[] = '*Fixture*';

        return $robotLoader;
    }
}
