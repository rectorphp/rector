<?php declare(strict_types=1);

namespace Rector\Php\Rector\ConstFetch;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use function Safe\sprintf;

/**
 * @see https://wiki.php.net/rfc/deprecate-bareword-strings
 * @see https://3v4l.org/56ZAu
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
        $fileInfo = $node->getAttribute(Attribute::FILE_INFO);
        if ($fileInfo === null) {
            throw new ShouldNotHappenException();
        }

        $this->undefinedConstants = [];
        $previousErrorHandler = set_error_handler(function ($severity, $message, $file, $line): void {
            $match = Strings::match($message, '#Use of undefined constant (?<constant>\w+)#');
            if ($match) {
                $this->undefinedConstants[] = $match['constant'];
            }
        });

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
        return new String_(sprintf('%s', $constantName));
    }
}
