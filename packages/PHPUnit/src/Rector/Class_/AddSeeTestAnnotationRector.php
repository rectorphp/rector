<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use Nette\Loaders\RobotLoader;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector\AddSeeTestAnnotationRectorTest
 */
final class AddSeeTestAnnotationRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var string[]
     */
    private $phpUnitTestCaseClasses = [];

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Add @see annotation test of the class for faster jump to test. Make it FQN, so it stays in the annotation, not in the PHP source code.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeService
{
}

class SomeServiceTest extends \PHPUnit\Framework\TestCase
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

class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
}
CODE_SAMPLE
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
        if ($this->shouldSkipClass($node)) {
            return null;
        }

        /** @var string $className */
        $className = $this->getName($node);

        $testCaseClassName = $this->resolveTestCaseClassName($className);
        if ($testCaseClassName === null) {
            return null;
        }

        $this->docBlockManipulator->addTag($node, $this->createSeePhpDocTagNode($testCaseClassName));

        return $node;
    }

    private function resolveTestCaseClassName(string $className): ?string
    {
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
        return new PhpDocTagNode('@see', new AttributeAwareGenericTagValueNode('\\' . $className));
    }

    private function shouldSkipClass(Class_ $class): bool
    {
        if ($class->isAnonymous()) {
            return true;
        }

        $className = $this->getName($class);
        if ($className === null) {
            return true;
        }

        // is a test case
        if (Strings::endsWith($className, 'Test')) {
            return true;
        }

        // is the @see annotation already added
        if ($class->getDocComment()) {
            /** @var string $docCommentText */
            $docCommentText = $class->getDocComment()->getText();

            /** @var string $shortClassName */
            $shortClassName = Strings::after($className, '\\', -1);
            $seeClassPattern = '#@see (.*?)' . preg_quote($shortClassName, '#') . 'Test#m';

            if (Strings::match($docCommentText, $seeClassPattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getPhpUnitTestCaseClasses(): array
    {
        if ($this->phpUnitTestCaseClasses) {
            return $this->phpUnitTestCaseClasses;
        }

        $robotLoader = new RobotLoader();

        // @todo make configurable + load from compsoer.json
        $robotLoader->addDirectory(getcwd() . '/src');
        $robotLoader->addDirectory(getcwd() . '/packages');
        $robotLoader->addDirectory(getcwd() . '/tests');

        $robotLoader->ignoreDirs[] = '*Expected*';
        $robotLoader->ignoreDirs[] = '*Fixture*';
        $robotLoader->ignoreDirs[] = '*vendor*';
        $robotLoader->rebuild();

        foreach (array_keys($robotLoader->getIndexedClasses()) as $class) {
            if (! Strings::endsWith($class, 'Test')) {
                continue;
            }

            $this->phpUnitTestCaseClasses[] = $class;
        }

        return $this->phpUnitTestCaseClasses;
    }
}
