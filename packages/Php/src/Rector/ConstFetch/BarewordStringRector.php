<?php declare(strict_types=1);

namespace Rector\Php\Rector\ConstFetch;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/deprecate-bareword-strings
 * @see https://3v4l.org/56ZAu
 * @see \Rector\Php\Tests\Rector\ConstFetch\BarewordStringRector\BarewordStringRectorTest
 */
final class BarewordStringRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $undefinedConstants = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes unquoted non-existing constants to strings', [
            new CodeSample('var_dump(VAR);', 'var_dump("VAR");'),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ConstFetch::class];
    }

    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        $constantName = (string) $node->name;
        if (defined($constantName)) {
            return null;
        }

        // load the file!
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo === null) {
            throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
        }

        $this->undefinedConstants = [];
        $previousErrorHandler = set_error_handler(
            function (int $severity, string $message, string $file, int $line): bool {
                $match = Strings::match($message, '#Use of undefined constant (?<constant>\w+)#');
                if ($match) {
                    $this->undefinedConstants[] = $match['constant'];
                }

                return true;
            }
        );

        // this duplicates the way composer handles it
        // @see https://github.com/composer/composer/issues/6232
        require_once $fileInfo->getRealPath();

        // restore
        if (is_callable($previousErrorHandler)) {
            set_error_handler($previousErrorHandler);
        }

        if (! in_array($constantName, $this->undefinedConstants, true)) {
            return null;
        }

        // wrap to explicit string
        return new String_($constantName);
    }
}
