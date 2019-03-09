<?php declare(strict_types=1);

namespace Rector\Rector\Constant;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RenameClassConstantRector extends AbstractRector
{
    /**
     * class => [
     *      OLD_CONSTANT => NEW_CONSTANT
     * ]
     *
     * @var string[][]
     */
    private $oldToNewConstantsByClass = [];

    /**
     * @param string[][] $oldToNewConstantsByClass
     */
    public function __construct(array $oldToNewConstantsByClass)
    {
        $this->oldToNewConstantsByClass = $oldToNewConstantsByClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined class constants in their calls.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$value = SomeClass::OLD_CONSTANT;
$value = SomeClass::OTHER_OLD_CONSTANT;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$value = SomeClass::NEW_CONSTANT;
$value = DifferentClass::NEW_CONSTANT;
CODE_SAMPLE
                ,
                ['SomeClass' => [
                    'OLD_CONSTANT' => 'NEW_CONSTANT',
                    'OTHER_OLD_CONSTANT' => 'DifferentClass::NEW_CONSTANT',
                ]]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConstFetch::class];
    }

    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->oldToNewConstantsByClass as $type => $oldToNewConstants) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($oldToNewConstants as $oldConstant => $newConstant) {
                if (! $this->isNameInsensitive($node->name, $oldConstant)) {
                    continue;
                }

                if (Strings::contains($newConstant, '::')) {
                    return $this->createClassConstantFetchNodeFromDoubleColonFormat($newConstant);
                }

                $node->name = new Identifier($newConstant);

                return $node;
            }
        }

        return $node;
    }

    private function createClassConstantFetchNodeFromDoubleColonFormat(string $constant): ClassConstFetch
    {
        [$constantClass, $constantName] = explode('::', $constant);

        return new ClassConstFetch(new FullyQualified($constantClass), new Identifier($constantName));
    }
}
