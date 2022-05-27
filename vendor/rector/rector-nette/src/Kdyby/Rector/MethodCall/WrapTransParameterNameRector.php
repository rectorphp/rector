<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StringUtils;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/doc/current/components/translation/usage.html#message-placeholders
 * @changelog https://github.com/Kdyby/Translation/blob/master/docs/en/index.md#placeholders
 * https://github.com/Kdyby/Translation/blob/6b0721c767a7be7f15b2fb13c529bea8536230aa/src/Translator.php#L172
 *
 * @see \Rector\Nette\Tests\Kdyby\Rector\MethodCall\WrapTransParameterNameRector\WrapTransParameterNameRectorTest
 */
final class WrapTransParameterNameRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/b8boED/1
     */
    private const BETWEEN_PERCENT_CHARS_REGEX = '#%(.*?)%#';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Adds %% to placeholder name of trans() method if missing', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Translation\Translator;

final class SomeController
{
    public function run()
    {
        $translator = new Translator('');
        $translated = $translator->trans(
            'Hello %name%',
            ['name' => $name]
        );
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Translation\Translator;

final class SomeController
{
    public function run()
    {
        $translator = new Translator('');
        $translated = $translator->trans(
            'Hello %name%',
            ['%name%' => $name]
        );
    }
}
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
        if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\Translation\\TranslatorInterface'))) {
            return null;
        }
        if (!$this->isName($node->name, 'trans')) {
            return null;
        }
        if (\count($node->args) < 2) {
            return null;
        }
        if (!$node->args[1]->value instanceof Array_) {
            return null;
        }
        /** @var Array_ $parametersArrayNode */
        $parametersArrayNode = $node->args[1]->value;
        foreach ($parametersArrayNode->items as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }
            if (!$arrayItem->key instanceof String_) {
                continue;
            }
            if (StringUtils::isMatch($arrayItem->key->value, self::BETWEEN_PERCENT_CHARS_REGEX)) {
                continue;
            }
            $arrayItem->key = new String_('%' . $arrayItem->key->value . '%');
        }
        return $node;
    }
}
