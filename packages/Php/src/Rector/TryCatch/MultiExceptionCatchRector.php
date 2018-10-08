<?php declare(strict_types=1);

namespace Rector\Php\Rector\TryCatch;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Printer\BetterStandardPrinter;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/multiple-catch
 */
final class MultiExceptionCatchRector extends AbstractRector
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes multi catch of same exception to single one | separated.',
            [
                new CodeSample(
<<<'CODE_SAMPLE'
try {
   // Some code...
} catch (ExceptionType1 $exception) {
   $sameCode;
} catch (ExceptionType2 $exception) {
   $sameCode;
}
CODE_SAMPLE
                    ,
<<<'CODE_SAMPLE'
try {
   // Some code...
} catch (ExceptionType1 | ExceptionType2 $exception) {
   $sameCode;
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [TryCatch::class];
    }

    /**
     * @param TryCatch $tryCatchNode
     */
    public function refactor(Node $tryCatchNode): ?Node
    {
        if (count($tryCatchNode->catches) < 2) {
            return $tryCatchNode;
        }

        $catchKeysByContent = $this->collectCatchKeysByContent($tryCatchNode);

        foreach ($catchKeysByContent as $keys) {
            // no duplicates
            if (count($keys) < 2) {
                continue;
            }

            $collectedTypes = $this->collectTypesFromCatchedByIds($tryCatchNode, $keys);
            $firstTryKey = array_shift($keys);
            $tryCatchNode->catches[$firstTryKey]->types = $collectedTypes;

            foreach ($keys as $key) {
                unset($tryCatchNode->catches[$key]);
            }
        }

        // reindex from 0 for printer
        $tryCatchNode->catches = array_values($tryCatchNode->catches);

        return $tryCatchNode;
    }

    /**
     * @return int[][]
     */
    private function collectCatchKeysByContent(TryCatch $tryCatchNode): array
    {
        $catchKeysByContent = [];
        foreach ($tryCatchNode->catches as $key => $catch) {
            $catchContent = $this->betterStandardPrinter->prettyPrint($catch->stmts);
            /** @var int $key */
            $catchKeysByContent[$catchContent][] = $key;
        }

        return $catchKeysByContent;
    }

    /**
     * @param int[] $keys
     * @return Name[]
     */
    private function collectTypesFromCatchedByIds(TryCatch $tryCatchNode, array $keys): array
    {
        $collectedTypes = [];

        foreach ($keys as $key) {
            $collectedTypes = array_merge($collectedTypes, $tryCatchNode->catches[$key]->types);
        }

        return $collectedTypes;
    }
}
