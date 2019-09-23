<?php declare(strict_types=1);

namespace Rector\Php73\Rector\String_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/flexible_heredoc_nowdoc_syntaxes
 * @see \Rector\Php73\Tests\Rector\String_\SensitiveHereNowDocRector\SensitiveHereNowDocRectorTest
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
                <<<'PHP'
$value = <<<A
    A
A
PHP
                ,
                <<<'PHP'
$value = <<<A_WRAP
    A
A_WRAP
PHP
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
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);

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
