<?php declare(strict_types=1);

namespace Rector\Reflection;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Parser\Parser;

final class FunctionReflectionResolver
{
    /**
     * @var string[]
     */
    private const POSSIBLE_CORE_STUB_LOCATIONS = [
        __DIR__ . '/../../../../jetbrains/phpstorm-stubs/Core/Core.php',
        __DIR__ . '/../../vendor/jetbrains/phpstorm-stubs/Core/Core.php',
    ];

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(Parser $parser, BetterNodeFinder $betterNodeFinder)
    {
        $this->parser = $parser;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function resolveCoreStubFunctionNode(string $nodeName): ?Function_
    {
        $stubFileLocation = $this->resolveCoreStubLocation();

        $nodes = $this->parser->parseFile($stubFileLocation);

        /** @var Function_|null $function */
        $function = $this->betterNodeFinder->findFirst($nodes, function (Node $node) use ($nodeName): bool {
            if (! $node instanceof Function_) {
                return false;
            }

            return (string) $node->name === $nodeName;
        });

        return $function;
    }

    private function resolveCoreStubLocation(): string
    {
        foreach (self::POSSIBLE_CORE_STUB_LOCATIONS as $possibleCoreStubLocation) {
            if (file_exists($possibleCoreStubLocation)) {
                /** @var string $possibleCoreStubLocation */
                return $possibleCoreStubLocation;
            }
        }

        throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
    }
}
