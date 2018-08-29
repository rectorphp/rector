<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Yaml;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Printer\BetterStandardPrinter;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ParseFileRector extends AbstractRector
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(NodeTypeResolver $nodeTypeResolver, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('session > use_strict_mode is true by default and can be removed', [
            new CodeSample('session > use_strict_mode: true', 'session:'),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * Process Node of matched type
     *
     * @param StaticCall $staticCallNode
     */
    public function refactor(Node $staticCallNode): ?Node
    {
        if ((string) $staticCallNode->name !== 'parse') {
            return null;
        }

        $staticCallNodeTypes = $this->nodeTypeResolver->resolve($staticCallNode->class);
        if (! in_array('Symfony\Component\Yaml\Yaml', $staticCallNodeTypes, true)) {
            return null;
        }

        if (! $this->isArgumentYamlFile($staticCallNode)) {
            return null;
        }

        $fileGetContentsFunCallNode = new FuncCall(new Name('file_get_contents'), [$staticCallNode->args[0]]);
        $staticCallNode->args[0] = new Arg($fileGetContentsFunCallNode);

        return $staticCallNode;
    }

    private function isArgumentYamlFile(StaticCall $staticCallNode): bool
    {
        $possibleFileNode = $staticCallNode->args[0]->value;
        $possibleFileNodeAsString = $this->betterStandardPrinter->prettyPrint([$possibleFileNode]);

        if (Strings::match($possibleFileNodeAsString, '#yml|yaml(\'|")$#')) {
            return true;
        }

        return false;
    }
}
