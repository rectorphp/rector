<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
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
            /** @var PhpDocInfo $phpDocInfo */
            $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);

            $dataProviderTags = $phpDocInfo->getTagsByName('dataProvider');
            if ($dataProviderTags === []) {
                continue;
            }

            foreach ($dataProviderTags as $dataProviderTag) {
                // @todo use custom annotation object!
                /** @var PhpDocTagNode $dataProviderTag */
                if (! $dataProviderTag->value instanceof GenericTagValueNode) {
                    continue;
                }

                $oldMethodName = $dataProviderTag->value->value;
                if (! Strings::startsWith($oldMethodName, 'test')) {
                    continue;
                }

                $newMethodName = $this->createNewMethodName($oldMethodName);

                // @todo create @dataProvider custom tag!
                /** @var AttributeAwareGenericTagValueNode $genericTagValueNode */
                $genericTagValueNode = $dataProviderTag->value;
                // change value - keep original for format preserving
                $genericTagValueNode->setAttribute('original_value', $genericTagValueNode->value);
                $genericTagValueNode->value = $newMethodName;

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
