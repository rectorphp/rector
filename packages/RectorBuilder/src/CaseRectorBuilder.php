<?php declare(strict_types=1);

namespace Rector\RectorBuilder;

use Rector\Contract\Rector\RectorInterface;

final class CaseRectorBuilder
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var string
     */
    private $newMethodName;

    /**
     * @var mixed[]
     */
    private $newArguments = [];

    public function matchMethodCallByType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function matchMethodName(string $methodName): self
    {
        $this->methodName = $methodName;

        return $this;
    }

    public function changeMethodNameTo(string $newMethodName): self
    {
        $this->newMethodName = $newMethodName;

        return $this;
    }

    public function addArgument(int $position, $value): self
    {
        $this->newArguments[$position] = $value;

        return $this;
    }

    public function create(): RectorInterface
    {
        // $caseRector = new CaseRector()

        dump('the work :)');
        die;
    }
}
