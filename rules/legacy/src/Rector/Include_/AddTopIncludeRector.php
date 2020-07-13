<?php

declare(strict_types=1);

namespace Rector\Legacy\Rector\Include_;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use ReflectionClass;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://github.com/rectorphp/rector/issues/3679
 *
 * @see \Rector\Legacy\Tests\Rector\Include_\AddTopIncludeRector\AddTopIncludeRectorTest
 */
final class AddTopIncludeRector extends AbstractFileSystemRector
{
    /**
     * @var string
     */
    private $file = '';

    /**
     * @var PhpParser\Node\Expr
     */
    private $fileExpression;

    /**
     * @var int
     */
    private $type = 0;

    /**
     * @var string[] to match
     */
    private $patterns = [];

    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var PhpParser\ParserFactory
     */
    private $parser;

    /**
     * @var PhpParser\PrettyPrinter\Standard
     */
    private $prettyPrinter;

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
        $parserFactory = new ParserFactory();
        $this->parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
        $this->prettyPrinter = new Standard();
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds an include file at the top of matching files but not class definitions', [
            new CodeSample(
                <<<'PHP'
<?php

if (isset($_POST["csrf"])) {
    processPost($_POST);
}
PHP,
                <<<'PHP'
<?php

include "autoloader.php";

if (isset($_POST["csrf"])) {
    processPost($_POST);
}
PHP,
                [
                    '$settings' => [
                        'type' => 'TYPE_INCLUDE_FILE',
                        'file' => '__DIR__ . "/../autoload.php"',
                        'match' => ['filePathWildCards'],
                    ],
                ]
            ),
        ]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $this->initialize();

        if (! $this->matchFile($smartFileInfo->getRealPath())) {
            return;
        }

        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        // we are done if there is a class definition in this file
        $classNode = $this->betterNodeFinder->findFirstInstanceOf($nodes, Class_::class);
        if ($classNode !== null) {
            return;
        }

        // find all includes and see if any match what we want to insert
        $includeNodes = $this->betterNodeFinder->findInstanceOf($nodes, Include_::class);
        foreach ($includeNodes as $includeNode) {
            if ($this->isTopFileInclude($includeNode)) {
                return;
            }
        }

        // add the include to the statements and print it
        array_unshift($nodes, new Nop());
        array_unshift($nodes, new Include_($this->fileExpression, $this->type));

        $this->print($nodes);
    }

    /**
     * Match file against matches, no patterns provided, then it matches
     */
    private function matchFile(string $path): bool
    {
        if ($this->patterns === []) {
            return true;
        }

        foreach ($this->patterns as $pattern) {
            if (fnmatch($pattern, $path, FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
    }

    private function initialize(): void
    {
        if ($this->type) {
            return;
        }
        $reflection = new ReflectionClass(Include_::class);
        $constants = $reflection->getConstants();

        if (! isset($constants[$this->settings['type'] ?? ''])) {
            throw new InvalidRectorsettingsException('Invalid type: must be one of ' . implode(
                ', ',
                array_keys($constants)
            ));
        }

        if ('' === ($this->settings['file'] ?? '')) {
            throw new InvalidRectorsettingsException('Invalid parameter: file must be provided');
        }
        $this->type = $constants[$this->settings['type']];
        $this->file = $this->settings['file'];
        $this->fileExpression = $this->getExpressionFromString($this->file);
        // normalize the file we are including
        $this->file = $this->getStringFromExpression($this->fileExpression);
        if (isset($this->settings['match']) && is_array($this->settings['match'])) {
            $this->patterns = $this->settings['match'];
        }
    }

    private function getExpressionFromString(string $source): ?Expr
    {
        $ast = $this->parser->parse("<?php {$source};\n");

        if (is_array($ast)) {
            return $ast[0]->expr;
        }

        return null;
    }

    private function getStringFromExpression(?Expr $expr): string
    {
        if ($expr === null) {
            return '';
        }

        return $this->prettyPrinter->prettyPrintExpr($expr);
    }

    private function isTopFileInclude(Include_ $includeNode): bool
    {
        return $this->file === $this->getSourceFromExpression($includeNode->expr);
    }
}
