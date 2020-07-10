<?php

declare(strict_types=1);

namespace Rector\Legacy\Rector\Include_;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\Exception\Rector\InvalidRectorConfigurationException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;

/**
 * @see https://github.com/rectorphp/rector/issues/3679
 *
 * @see \Rector\Legacy\Tests\Rector\Include_\AddTopIncludeRector\AddTopIncludeRectorTest
 */
final class AddTopIncludeRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $completed = false;

    /**
     * @var string
     */
    private $file = '';

    /**
     * @var string
     */
    private $lastFileProcessed = '';

    /**
     * @var int
     */
    private $type = 0;

    /**
     * @var string[]
     */
    private $classNodes = [
        Declare_::class => true,
        DeclareDeclare::class => true,
        GroupUse::class => true,
        InlineHTML::class => true,
        Namespace_::class => true,
        Use_::class => true,
        UseUse::class => true,
    ];

    /**
     * @var string[] to match
     */
    private $patterns = [];

    /**
     * @var array
     */
    private $configuration = [];

    public function __construct(array $configuration = [])
    {
        $this->configuration = $configuration;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds an include file at the top of matching files but not class definitions', [
            new CodeSample(
                '<?php

if (isset($_POST["csrf"])) {
    processPost($_POST);
}',
                '<?php

include "autoloader.php";

if (isset($_POST["csrf"])) {
    processPost($_POST);
}',
                [
                    '$configuration' => [
                        'type' => 'TYPE_INCLUDE_FILE',
                        'file' => 'autoload.php',
                        'match' => ['filePathWildCards'],
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        $this->initialize();

        return [Node::class];
    }

    public function refactor(Node $node): ?Node
    {
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        $path = $fileInfo ? $fileInfo->getRelativeFilePath() : '';

        if ($path !== $this->lastFileProcessed) {
            $this->lastFileProcessed = $path;
            $this->completed = false;
        }

        if (! $this->matchFile($path)) {
            $this->completed = true;

            return null;
        }

        $name = get_class($node);
        // things that are OK at the top of a file
        if ($this->completed || isset($this->classNodes[$name])) {
            return null;
        }

        // if we find a class or include, then no include needed
        if ($name === Class_::class) {
            $this->completed = true;

            return null;
        }

        $this->completed = true;

        $expr = new String_($this->file);
        $includeNode = new Include_($expr, $this->type);

        $this->addNodeBeforeNode($includeNode, $node);
        $this->addNodeBeforeNode(new Nop(), $node);

        return $node;
    }

    /**
     * Match file against matches, no patterns provided, then it matches
     */
    private function matchFile(
        string $path
    ): bool {
        if (empty($this->patterns)) {
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

        if (empty($constants[$this->configuration['type'] ?? ''])) {
            throw new InvalidRectorConfigurationException('Invalid type: must be one of ' . implode(
                ', ',
                array_keys($constants)
            ));
        }

        if (empty($this->configuration['file'] ?? '')) {
            throw new InvalidRectorConfigurationException('Invalid parameter: file must be provided');
        }
        $this->type = $constants[$this->configuration['type']];
        $this->file = $this->configuration['file'];
        if (isset($this->configuration['match']) && is_array($this->configuration['match'])) {
            $this->patterns = $this->configuration['match'];
        }
    }
}
