<?php declare(strict_types=1);

namespace Rector\Php\Rector\String_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/flexible_heredoc_nowdoc_syntaxes
 */
final class SensitiveHereNowDocRector extends AbstractRector
{
    /**
     * @var string
     */
    private const WRAP_SUFFIX = '_WRAP';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes heredoc/nowdoc that contains closing word to safe wrapper name', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$value = <<<A
    A
A
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$value = <<<A_WRAP
    A
A_WRAP
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    /**
     * @param String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! in_array($node->getAttribute('kind'), [String_::KIND_HEREDOC, String_::KIND_NOWDOC], true)) {
            return null;
        }

        // the doc label is not in the string → ok
        /** @var string $docLabel */
        $docLabel = $node->getAttribute('docLabel');

        if (! Strings::contains($node->value, $docLabel)) {
            return null;
        }

        $node->setAttribute('docLabel', $this->uniquateDocLabel($node->value, $docLabel));

        // invoke redraw
        $node->setAttribute(Attribute::ORIGINAL_NODE, null);

        return $node;
    }

    private function uniquateDocLabel(string $value, string $docLabel): string
    {
        $docLabel .= self::WRAP_SUFFIX;
        $docLabelCounterTemplate = $docLabel . '_%d';

        $i = 0;
        while (Strings::contains($value, $docLabel)) {
            $docLabel = sprintf($docLabelCounterTemplate, ++$i);
        }

        return $docLabel;
    }
}
