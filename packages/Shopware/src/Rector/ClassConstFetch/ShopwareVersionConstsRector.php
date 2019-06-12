<?php declare(strict_types=1);

namespace Rector\Shopware\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Fixture\BlablaSpec;

/**
 * @see https://github.com/shopware/shopware/blob/5.6/UPGRADE-5.6.md
 */
final class ShopwareVersionConstsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use version from di parameter', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        echo \Shopware::VERSION;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        echo Shopware()->Container()->getParameter('shopware.release.version');
    }
}
CODE_SAMPLE

            )
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
        if (! $this->isType($node, 'Shopware')) {
            return null;
        }

        if (! $node->name instanceof Node\Identifier) {
            return null;
        }

        switch ($node->name->name) {
            case 'VERSION':
                return $this->buildParameterCall('shopware.release.version');
            case 'VERSION_TEXT':
                return $this->buildParameterCall('shopware.release.version_text');
            case 'REVISION':
                return $this->buildParameterCall('shopware.release.revision');
            default:
                return null;
        }
    }

    private function buildParameterCall(string $paramterName): Node\Expr\MethodCall
    {
        $shopwareFunction = new Node\Expr\FuncCall(new Node\Name('Shopware'));
        $containerCall = new Node\Expr\MethodCall($shopwareFunction, new Node\Identifier('Container'));
        return new Node\Expr\MethodCall($containerCall, new Node\Identifier('getParameter'), [new Node\Arg(new Node\Scalar\String_($paramterName))]);
    }
}
