<?php declare(strict_types=1);

namespace Rector\Rector\Constant;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RenameClassConstantsUseToStringsRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $oldConstantsToNewValuesByType = [];

    /**
     * @param string[][] $oldConstantsToNewValuesByType
     */
    public function __construct(array $oldConstantsToNewValuesByType)
    {
        $this->oldConstantsToNewValuesByType = $oldConstantsToNewValuesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces constant by value', [
            new ConfiguredCodeSample(
                '$value === Nette\Configurator::DEVELOPMENT',
                '$value === "development"',
                [
                    'Nette\Configurator' => [
                        'DEVELOPMENT' => 'development',
                        'PRODUCTION' => 'production',
                    ],
                ]
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
        $className = $this->getClassNameFromClassConstFetch($node);

        foreach ($this->oldConstantsToNewValuesByType as $type => $oldConstantsToNewValues) {
            if ($className !== $type) {
                continue;
            }

            foreach ($oldConstantsToNewValues as $oldConstant => $newValue) {
                if ((string) $node->name === $oldConstant) {
                    return new String_($newValue);
                }
            }
        }

        return $node;
    }

    private function getClassNameFromClassConstFetch(ClassConstFetch $classConstFetchNode): string
    {
        /** @var FullyQualified|null $fqnName */
        $fqnName = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME);

        if ($fqnName === null && $classConstFetchNode->class instanceof Variable) {
            return $this->getName($classConstFetchNode->class);
        }

        if ($fqnName !== null) {
            return $fqnName->toString();
        }
    }
}
