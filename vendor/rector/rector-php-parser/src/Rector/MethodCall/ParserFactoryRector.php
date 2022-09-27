<?php

declare (strict_types=1);
namespace Rector\PhpParser\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-5.0.md#changes-to-the-parser-factory
 *
 * @see \Rector\PhpParser\Tests\Rector\MethodCall\ParserFactoryRector\ParserFactoryRectorTest
 */
final class ParserFactoryRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Upgrade ParserFactory create() method', [new CodeSample(<<<'CODE_SAMPLE'
use PhpParser\ParserFactory;

$factory = new ParserFactory;

$parser = $factory->create(ParserFactory::PREFER_PHP7);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PhpParser\ParserFactory;

$factory = new ParserFactory;

$parser = $factory->createForNewestSupportedVersion();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node->var, new ObjectType('PhpParser\\ParserFactory'))) {
            return null;
        }
        if (!$this->isName($node->name, 'create')) {
            return null;
        }
        $args = $node->getArgs();
        $value = $this->valueResolver->getValue($args[0]->value);
        if ($value === 1) {
            $node->name = new Identifier('createForNewestSupportedVersion');
            $node->args = [];
            return $node;
        }
        if ($value === 4) {
            $node->name = new Identifier('createForVersion');
            $fullyQualified = new FullyQualified('PhpParser\\PhpVersion');
            $fromStringStaticCall = new StaticCall($fullyQualified, 'fromString', [new Arg(new String_('5.6'))]);
            $node->args = [new Arg($fromStringStaticCall)];
            return $node;
        }
        return null;
    }
}
