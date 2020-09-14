<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareDataProviderTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://stackoverflow.com/a/46693675/1348344
 *
 * @see \Rector\PHPUnit\Tests\Rector\Class_\RemoveDataProviderTestPrefixRector\RemoveDataProviderTestPrefixRectorTest
 */
final class RemoveDataProviderTestPrefixRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $providerMethodNamesToNewNames = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Data provider methods cannot start with "test" prefix', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
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
            /** @var PhpDocInfo|null $phpDocInfo */
            $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($phpDocInfo === null) {
                continue;
            }

            /** @var AttributeAwareDataProviderTagValueNode[] $dataProviderTagValueNodes */
            $dataProviderTagValueNodes = $phpDocInfo->findAllByType(AttributeAwareDataProviderTagValueNode::class);
            if ($dataProviderTagValueNodes === []) {
                continue;
            }

            foreach ($dataProviderTagValueNodes as $dataProviderTagValueNode) {
                $oldMethodName = $dataProviderTagValueNode->getMethod();
                if (! Strings::startsWith($oldMethodName, 'test')) {
                    continue;
                }

                $newMethodName = $this->createNewMethodName($oldMethodName);
                $dataProviderTagValueNode->changeMethod($newMethodName);

                $oldMethodName = trim($oldMethodName, '()');
                $newMethodName = trim($newMethodName, '()');

                $this->providerMethodNamesToNewNames[$oldMethodName] = $newMethodName;
            }
        }
    }

    private function renameProviderMethods(Class_ $class): void
    {
        foreach ($class->getMethods() as $classMethod) {
            foreach ($this->providerMethodNamesToNewNames as $oldName => $newName) {
                if (! $this->isName($classMethod, $oldName)) {
                    continue;
                }

                $classMethod->name = new Identifier($newName);
            }
        }
    }

    private function createNewMethodName(string $oldMethodName): string
    {
        $newMethodName = Strings::substring($oldMethodName, strlen('test'));

        return lcfirst($newMethodName);
    }
}
