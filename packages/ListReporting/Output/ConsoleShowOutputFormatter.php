<?php

declare (strict_types=1);
namespace Rector\ListReporting\Output;

use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\ListReporting\Contract\Output\ShowOutputFormatterInterface;
final class ConsoleShowOutputFormatter implements \Rector\ListReporting\Contract\Output\ShowOutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'console';
    /**
     * @var \Rector\Core\Contract\Console\OutputStyleInterface
     */
    private $outputStyle;
    public function __construct(\Rector\Core\Contract\Console\OutputStyleInterface $outputStyle)
    {
        $this->outputStyle = $outputStyle;
    }
    /**
     * @param mixed[] $rectors
     */
    public function list($rectors) : void
    {
        $rectorCount = \count($rectors);
        $this->outputStyle->title('Loaded Rector rules');
        foreach ($rectors as $rector) {
            $this->outputStyle->writeln(' * ' . \get_class($rector));
        }
        $message = \sprintf('%d loaded Rectors', $rectorCount);
        $this->outputStyle->success($message);
    }
    public function getName() : string
    {
        return self::NAME;
    }
}
