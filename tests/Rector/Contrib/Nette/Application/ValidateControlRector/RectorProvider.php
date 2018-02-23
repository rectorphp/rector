<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\Application\ValidateControlRector;

use Rector\Contract\Rector\RectorInterface;
use Rector\RectorBuilder\CaseRectorBuilder;
use Rector\RectorBuilder\Contract\RectorProviderInterface;

final class RectorProvider implements RectorProviderInterface
{
    /**
     * @var CaseRectorBuilder
     */
    private $caseRectorBuilder;

    public function __construct(CaseRectorBuilder $caseRectorBuilder)
    {
        $this->caseRectorBuilder = $caseRectorBuilder;
    }

    public function provide(): RectorInterface
    {
        return $this->caseRectorBuilder->matchMethodCallByType('@todo')
            ->matchMethodName('@todo')
            ->changeMethodNameTo('@todo')
            ->addArgument(2, '@todo')
            ->create();
    }
}
