<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Yaml;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ParseFileRector extends AbstractRector
{
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
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'parse')) {
            return null;
        }

        if (! $this->isType($node->class, 'Symfony\Component\Yaml\Yaml')) {
            return null;
        }

        if (! $this->isArgumentYamlFile($node)) {
            return null;
        }

        $fileGetContentsFunCallNode = new FuncCall(new Name('file_get_contents'), [$node->args[0]]);
        $node->args[0] = new Arg($fileGetContentsFunCallNode);

        return $node;
    }

    private function isArgumentYamlFile(StaticCall $staticCallNode): bool
    {
        $possibleFileNode = $staticCallNode->args[0]->value;

        $possibleFileNodeAsString = $this->print($possibleFileNode);

        // is yml/yaml file
        if (Strings::match($possibleFileNodeAsString, '#\.(yml|yaml)(\'|")$#')) {
            return true;
        }

        // is probably a file variable
        if (Strings::match($possibleFileNodeAsString, '#\File$#')) {
            return true;
        }

        // try to detect current value
        /** @var Scope $nodeScope */
        $nodeScope = $possibleFileNode->getAttribute(Attribute::SCOPE);
        $nodeType = $nodeScope->getType($possibleFileNode);

        if ($nodeType instanceof ConstantStringType) {
            if (Strings::match($nodeType->getValue(), '#\.(yml|yaml)$#')) {
                return true;
            }
        }

        return false;
    }
}
