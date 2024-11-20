<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\ValueObject;

use Rector\Exception\ShouldNotHappenException;
final class StartAndEnd
{
    /**
     * @readonly
     */
    private int $start;
    /**
     * @readonly
     */
    private int $end;
    public function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
        if ($end < $start) {
            throw new ShouldNotHappenException();
        }
    }
    public function getStart() : int
    {
        return $this->start;
    }
    public function getEnd() : int
    {
        return $this->end;
    }
}
