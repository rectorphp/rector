<?php declare(strict_types=1);

namespace Rector\PhpParser\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * Covers part of https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-4.0.md
 */
final class IdentifierRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $typeToPropertiesMap = [
        'PhpParser\Node\Const_' => ['name'],
        'PhpParser\Node\NullableType' => ['type'], // sometimes only
        'PhpParser\Node\Param' => ['type'],  // sometimes only
        'PhpParser\Node\Expr\ClassConstFetch' => ['name'],
        'PhpParser\Node\Expr\Closure' => ['returnType'], // sometimes only
        'PhpParser\Node\Expr\MethodCall' => ['name'],
        'PhpParser\Node\Expr\PropertyFetch' => ['name'],
        'PhpParser\Node\Expr\StaticCall' => ['name'],
        'PhpParser\Node\Expr\StaticPropertyFetch' => ['name'],
        'PhpParser\Node\Stmt\Class_' => ['name'],
        'PhpParser\Node\Stmt\ClassMethod' => ['name', 'returnType' /* sometimes only */],
        'PhpParser\Node\Stmt\Function' => ['name', 'returnType' /* sometimes only */],
        'PhpParser\Node\Stmt\Goto_' => ['name'],
        'PhpParser\Node\Stmt\Interface_' => ['name'],
        'PhpParser\Node\Stmt\Label' => ['name'],
        'PhpParser\Node\Stmt\PropertyProperty' => ['name'],
        'PhpParser\Node\Stmt\TraitUseAdaptation\Alias' => ['method', 'newName'],
        'PhpParser\Node\Stmt\TraitUseAdaptation\Precedence' => ['method'],
        'PhpParser\Node\Stmt\Trait_' => ['name'],
        'PhpParser\Node\Stmt\UseUse' => ['alias'],
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns node string names to Identifier object in php-parser', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$constNode = new PhpParser\Node\Const_;
$name = $constNode->name;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$constNode = new PhpParser\Node\Const_;
$name = $constNode->name->toString();'
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [PropertyFetch::class];
    }

    /**
     * @param PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isTypes($node, array_keys($this->typeToPropertiesMap))) {
            return null;
        }

        foreach ($this->typeToPropertiesMap as $type => $properties) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            if (! $this->isNames($node, $properties)) {
                continue;
            }

            $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
            if ($parentNode instanceof MethodCall) {
                continue;
            }

            return $this->createMethodCall($node, 'toString');
        }

        return null;
    }
}
