<?php declare(strict_types=1);

namespace Rector\Nette\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://www.tomasvotruba.cz/blog/2019/02/07/what-i-learned-by-using-thecodingmachine-safe/#is-there-a-better-way
 */
final class PregFunctionToNetteUtilsStringsRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $functionNameToMethodName = [
        'preg_match' => 'match',
        'preg_match_all' => 'matchAll',
        'preg_split' => 'split',
        'preg_replace' => 'replace',
        'preg_replace_callback' => 'replace',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use Nette\Utils\Strings over bare preg_* functions', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        preg_match('#Hi#', $content);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        \Nette\Utils\Strings::match($content, '#Hi#');
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isNames($node, array_keys($this->functionNameToMethodName))) {
            return null;
        }

        $methodName = $this->functionNameToMethodName[$this->getName($node)];
        $matchStaticCall = $this->createMatchStaticCall($node, $methodName);

        // skip assigns, might be used with different return value
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign) {
            if ($methodName === 'matchAll') {
                // use count
                return new FuncCall(new Name('count'), [new Arg($matchStaticCall)]);
            }

            if ($methodName === 'split') {
                return $this->processSplit($node, $matchStaticCall);
            }

            if ($methodName === 'replace') {
                return $matchStaticCall;
            }

            return null;
        }

        // assign
        if (isset($node->args[2])) {
            return new Assign($node->args[2]->value, $matchStaticCall);
        }

        return $matchStaticCall;
    }

    private function createMatchStaticCall(FuncCall $funcCall, string $methodName): StaticCall
    {
        $args = [];

        if ($methodName === 'replace') {
            $args[] = $funcCall->args[2];
            $args[] = $funcCall->args[0];
            $args[] = $funcCall->args[1];
        } else {
            $args[] = $funcCall->args[1];
            $args[] = $funcCall->args[0];
        }

        return $this->createStaticCall('Nette\Utils\Strings', $methodName, $args);
    }

    private function processSplit(FuncCall $funcCall, StaticCall $matchStaticCall): Node
    {
        if (isset($funcCall->args[2])) {
            if ($this->isValue($funcCall->args[2]->value, -1)) {
                if (isset($funcCall->args[3])) {
                    $matchStaticCall->args[] = $funcCall->args[3];
                }

                return $matchStaticCall;
            }

            return $funcCall;
        }

        return $matchStaticCall;
    }
}
