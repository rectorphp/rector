<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\MethodCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://symfony.com/doc/current/components/translation/usage.html#message-placeholders
 * @see https://github.com/Kdyby/Translation/blob/master/docs/en/index.md#placeholders
 * https://github.com/Kdyby/Translation/blob/6b0721c767a7be7f15b2fb13c529bea8536230aa/src/Translator.php#L172
 */
final class WrapTransParameterNameRector extends AbstractRector
{
    /**
     * @var string
     */
    private $translatorClass;

    public function __construct(string $translatorClass = 'Symfony\Component\Translation\TranslatorInterface')
    {
        $this->translatorClass = $translatorClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds %% to placeholder name of trans() method if missing', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
                ,
                <<<'CODE_SAMPLE'
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
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, $this->translatorClass)) {
            return null;
        }

        if (! $this->isName($node, 'trans')) {
            return null;
        }

        if (count($node->args) < 2) {
            return null;
        }

        if (! $node->args[1]->value instanceof Array_) {
            return null;
        }

        /** @var Array_ $parametersArrayNode */
        $parametersArrayNode = $node->args[1]->value;

        foreach ($parametersArrayNode->items as $arrayItem) {
            if (! $arrayItem->key instanceof String_) {
                continue;
            }

            if (Strings::match($arrayItem->key->value, '#%(.*?)%#')) {
                continue;
            }

            $arrayItem->key = new String_('%' . $arrayItem->key->value . '%');
        }

        return $node;
    }
}
