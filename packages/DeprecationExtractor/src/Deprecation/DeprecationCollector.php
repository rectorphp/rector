<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Deprecation;

use PhpParser\Node;
use PhpParser\Node\Arg;
use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;
use Rector\DeprecationExtractor\Transformer\ArgumentToDeprecationTransformer;
use Rector\DeprecationExtractor\Transformer\MessageToDeprecationTransformer;

final class DeprecationCollector
{
    /**
     * Collected from "deprecated" annotations
     *
     * @var string[]|Node[]
     */
    private $deprecationMessages = [];

    /**
     * Collected from trigger_error(*, E_USER_DEPRECATED) function calls
     *
     * @var Arg[]
     */
    private $deprecationArgNodes = [];

    /**
     * @var DeprecationInterface[]
     */
    private $deprecations = [];

    /**
     * @var bool
     */
    private $areDeprecationsTransformed = false;

    /**
     * @var MessageToDeprecationTransformer
     */
    private $messageToDeprecationTransformer;

    /**
     * @var ArgumentToDeprecationTransformer
     */
    private $argumentToDeprecationTransformer;

    public function __construct(
        MessageToDeprecationTransformer $messageToDeprecationTransformer,
        ArgumentToDeprecationTransformer $argumentToDeprecationTransformer
    ) {
        $this->messageToDeprecationTransformer = $messageToDeprecationTransformer;
        $this->argumentToDeprecationTransformer = $argumentToDeprecationTransformer;
    }

    public function addDeprecationMessage(string $deprecationMessage, Node $node): void
    {
        $this->deprecationMessages[] = [
            'message' => $deprecationMessage,
            'node' => $node,
        ];
    }

    public function addDeprecationArgNode(Arg $argNode): void
    {
        $this->deprecationArgNodes[] = $argNode;
    }

    /**
     * @return string[]|Node[]
     */
    public function getDeprecationMessages(): array
    {
        return $this->deprecationMessages;
    }

    /**
     * @return Arg[]
     */
    public function getDeprecationArgNodes(): array
    {
        return $this->deprecationArgNodes;
    }

    public function addDeprecation(DeprecationInterface $deprecation): void
    {
        $this->deprecations[] = $deprecation;
    }

    /**
     * @return DeprecationInterface[]
     */
    public function getDeprecations(): array
    {
        if (! $this->areDeprecationsTransformed) {
            $this->transformDeprecations();
        }

        $this->areDeprecationsTransformed = true;

        return $this->deprecations;
    }

    /**
     * perform on getDeprecationArgNodes/addDeprecationMessage?
     */
    private function transformDeprecations(): void
    {
        foreach ($this->deprecationMessages as $deprecationMessage) {
            $deprecation = $this->messageToDeprecationTransformer->transform(
                $deprecationMessage['message'],
                $deprecationMessage['node']
            );

            if ($deprecation) {
                $this->addDeprecation($deprecation);
            }
        }

        foreach ($this->deprecationArgNodes as $deprecationArgNode) {
            $deprecation = $this->argumentToDeprecationTransformer->transform($deprecationArgNode);

            if ($deprecation) {
                $this->addDeprecation($deprecation);
            }
        }
    }
}
