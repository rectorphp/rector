<?php

declare(strict_types=1);

namespace Rector\Symfony2\Rector\StaticCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Symfony2\Tests\Rector\StaticCall\ParseFileRector\ParseFileRectorTest
 */
final class ParseFileRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/ZaY42i/1
     */
    private const YAML_SUFFIX_IN_QUOTE_REGEX = '#\.(yml|yaml)(\'|\")$#';

    /**
     * @var string
     * @see https://regex101.com/r/YHA05g/1
     */
    private const FILE_SUFFIX_REGEX = '#File$#';

    /**
     * @var string
     * @see https://regex101.com/r/JmNhZj/1
     */
    private const YAML_SUFFIX_REGEX = '#\.(yml|yaml)$#';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'session > use_strict_mode is true by default and can be removed',
            [new CodeSample('session > use_strict_mode: true', 'session:')]);
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
        if (! $this->isName($node->name, 'parse')) {
            return null;
        }

        if (! $this->isObjectType($node->class, 'Symfony\Component\Yaml\Yaml')) {
            return null;
        }

        if (! $this->isArgumentYamlFile($node)) {
            return null;
        }

        $funcCall = $this->nodeFactory->createFuncCall('file_get_contents', [$node->args[0]]);
        $node->args[0] = new Arg($funcCall);

        return $node;
    }

    private function isArgumentYamlFile(StaticCall $staticCall): bool
    {
        $possibleFileNode = $staticCall->args[0]->value;

        $possibleFileNodeAsString = $this->print($possibleFileNode);

        // is yml/yaml file
        if (Strings::match($possibleFileNodeAsString, self::YAML_SUFFIX_IN_QUOTE_REGEX)) {
            return true;
        }

        // is probably a file variable
        if (Strings::match($possibleFileNodeAsString, self::FILE_SUFFIX_REGEX)) {
            return true;
        }

        // try to detect current value
        $nodeScope = $possibleFileNode->getAttribute(AttributeKey::SCOPE);
        if (! $nodeScope instanceof Scope) {
            throw new ShouldNotHappenException();
        }

        $nodeType = $nodeScope->getType($possibleFileNode);
        if (! $nodeType instanceof ConstantStringType) {
            return false;
        }
        return (bool) Strings::match($nodeType->getValue(), self::YAML_SUFFIX_REGEX);
    }
}
