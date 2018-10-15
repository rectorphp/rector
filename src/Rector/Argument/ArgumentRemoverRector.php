<?php declare(strict_types=1);

namespace Rector\Rector\Argument;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Rector\ArgumentRemoverRecipe;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use SomeClass;

final class ArgumentRemoverRector extends AbstractArgumentRector
{
    /**
     * @var ArgumentRemoverRecipe[]
     */
    private $recipes = [];

    /**
     * @var ArgumentRemoverRecipe[]
     */
    private $activeRecipes = [];

    /**
     * @param mixed[] $argumentChangesByMethodAndType
     */
    public function __construct(array $argumentChangesByMethodAndType)
    {
        foreach ($argumentChangesByMethodAndType as $configurationArray) {
            $this->recipes[] = ArgumentRemoverRecipe::createFromArray($configurationArray);
        }
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes defined arguments in defined methods and their calls.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(true);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod();'
CODE_SAMPLE
                    ,
                    [
                        '$argumentChangesByMethodAndType' => [
                            'class' => SomeClass::class,
                            'method' => 'someMethod',
                            'position' => 0,
                            'value' => 'true',
                        ],
                    ]
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->activeRecipes = $this->matchArgumentChanges($node);
        if (! (bool) $this->activeRecipes) {
            return null;
        }
        $argumentsOrParameters = $this->getNodeArgumentsOrParameters($node);
        $argumentsOrParameters = $this->processArgumentNodes($argumentsOrParameters);

        $this->setNodeArgumentsOrParameters($node, $argumentsOrParameters);

        return $node;
    }

    /**
     * @return ArgumentRemoverRecipe[]
     */
    private function matchArgumentChanges(Node $node): array
    {
        $argumentReplacerRecipes = [];

        foreach ($this->recipes as $argumentRemoverRecipe) {
            if ($this->isNodeToRecipeMatch($node, $argumentRemoverRecipe)) {
                $argumentReplacerRecipes[] = $argumentRemoverRecipe;
            }
        }

        return $argumentReplacerRecipes;
    }

    /**
     * @param Arg[]|Param[] $argumentNodes
     * @return mixed[]
     */
    private function processArgumentNodes(array $argumentNodes): array
    {
        foreach ($this->activeRecipes as $activeArgumentRemoverRecipe) {
            $position = $activeArgumentRemoverRecipe->getPosition();

            if ($activeArgumentRemoverRecipe->getValue() === null) {
                unset($argumentNodes[$position]);
            } elseif ($this->isArgumentValueMatch($argumentNodes[$position], $activeArgumentRemoverRecipe)) {
                unset($argumentNodes[$position]);
            }
        }

        return $argumentNodes;
    }

    /**
     * @param Arg|Param $argOrParamNode
     */
    private function isArgumentValueMatch($argOrParamNode, ArgumentRemoverRecipe $argumentRemoverRecipe): bool
    {
        // only argument specific value can be removed
        if (! $argOrParamNode instanceof Arg) {
            return false;
        }

        $valueNode = $argOrParamNode->value;

        if ($valueNode instanceof ClassConstFetch) {
            $valueNodeAsString = $valueNode->class->getAttribute(Attribute::RESOLVED_NAME)->toString()
                . '::'
                . (string) $valueNode->name;

            if ($valueNodeAsString === $argumentRemoverRecipe->getValue()) {
                return true;
            }
        }

        return false;
    }
}
