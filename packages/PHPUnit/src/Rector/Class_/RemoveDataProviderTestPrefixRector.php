<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/a/46693675/1348344
 *
 * @see \Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector\RemoveDataProviderTestPrefixRectorTest
 */
final class RemoveDataProviderTestPrefixRector extends AbstractPHPUnitRector
{
    /**
     * @var string
     */
    private const DATA_PROVIDER_ANNOTATION_PATTERN = '#(@dataProvider\s+)(?<providerMethodName>test\w+)#';

    /**
     * @var string
     */
    private const DATA_PROVIDER_EXACT_NAME_PATTERN = '#(@dataProvider\s+)(%s)#';

    /**
     * @var string[]
     */
    private $providerMethodNamesToNewNames = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Data provider methods cannot start with "test" prefix', [
            new CodeSample(
                <<<'PHP'
class SomeClass extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider testProvideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function testProvideData()
    {
        return ['123'];
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test()
    {
        $nothing = 5;
    }

    public function provideData()
    {
        return ['123'];
    }
}
PHP
            ),
        ]);
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
        if (! $this->isInTestClass($node)) {
            return null;
        }

        $this->providerMethodNamesToNewNames = [];

        $this->renameDataProviderAnnotationsAndCollectRenamedMethods($node);

        $this->renameProviderMethods($node);

        return $node;
    }

    private function renameDataProviderAnnotationsAndCollectRenamedMethods(Class_ $class): void
    {
        foreach ($class->getMethods() as $classMethod) {
            if ($classMethod->getDocComment() === null) {
                continue;
            }

            $docCommentText = $classMethod->getDocComment()->getText();
            if (! Strings::match($docCommentText, self::DATA_PROVIDER_ANNOTATION_PATTERN)) {
                continue;
            }

            // replace the name in the doc
            $matches = Strings::matchAll($docCommentText, self::DATA_PROVIDER_ANNOTATION_PATTERN);
            foreach ($matches as $match) {
                $currentProviderMethodName = $match['providerMethodName'];

                $newMethodName = Strings::substring($currentProviderMethodName, strlen('test'));
                $newMethodName = lcfirst($newMethodName);

                $currentMethodPattern = sprintf(self::DATA_PROVIDER_EXACT_NAME_PATTERN, $currentProviderMethodName);

                $docCommentText = Strings::replace($docCommentText, $currentMethodPattern, '$1' . $newMethodName);

                $this->providerMethodNamesToNewNames[$currentProviderMethodName] = $newMethodName;
            }

            $classMethod->setDocComment(new Doc($docCommentText));
        }
    }

    private function renameProviderMethods(Class_ $class): void
    {
        foreach ($class->getMethods() as $classMethod) {
            foreach ($this->providerMethodNamesToNewNames as $oldName => $newName) {
                if (! $this->isName($classMethod, $oldName)) {
                    continue;
                }

                $classMethod->name = new Node\Identifier($newName);
            }
        }
    }
}
