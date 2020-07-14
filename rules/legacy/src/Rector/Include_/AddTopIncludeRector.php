<?php

declare(strict_types=1);

namespace Rector\Legacy\Rector\Include_;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Rector\Core\Exception\Rector\InvalidRectorConfigurationException;
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
     * @var ?Expr
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
     * @var Parser
     */
    private $parser;

    /**
     * @var Standard
     */
    private $prettyPrinter;

    public function __construct(string $type = 'TYPE_INCLUDE', string $file = '"autoload.php"', array $match = [])
    {
		$this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		$this->prettyPrinter = new Standard();
		$reflection = new ReflectionClass(Include_::class);
		$constants = $reflection->getConstants();

		if (! isset($constants[$type])) {
			throw new InvalidRectorConfigurationException('Invalid type: must be one of ' . implode(
				', ',
				array_keys($constants)
			));
			$this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
			$this->prettyPrinter = new Standard();
		}

		$this->type = $constants[$type];
		$this->file = $file;
		$this->fileExpression = $this->getExpressionFromString($this->file);
		// normalize the file we are including
		$this->file = $this->getStringFromExpression($this->fileExpression);
  		$this->patterns = $match;
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
PHP
            ),
        ]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        if (! $this->matchFile($smartFileInfo->getRelativeFilePath()) || $this->fileExpression === null) {
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
        array_unshift($nodes, new Expression(new Include_($this->fileExpression, $this->type)));

        $this->printNodesToFilePath($nodes, $smartFileInfo->getRelativeFilePath());
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

    private function getExpressionFromString(string $source): ?Expr
    {
        $nodes = $this->parser->parse('<?php ' . $source . ';');

        if (is_array($nodes)) {
            return $nodes[0]->expr;
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
        return $this->file === $this->getStringFromExpression($includeNode->expr);
    }
}
