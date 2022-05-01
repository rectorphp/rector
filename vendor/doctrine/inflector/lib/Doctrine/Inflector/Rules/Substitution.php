<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Doctrine\Inflector\Rules;

final class Substitution
{
    /** @var Word */
    private $from;
    /** @var Word */
    private $to;
    public function __construct(\RectorPrefix20220501\Doctrine\Inflector\Rules\Word $from, \RectorPrefix20220501\Doctrine\Inflector\Rules\Word $to)
    {
        $this->from = $from;
        $this->to = $to;
    }
    public function getFrom() : \RectorPrefix20220501\Doctrine\Inflector\Rules\Word
    {
        return $this->from;
    }
    public function getTo() : \RectorPrefix20220501\Doctrine\Inflector\Rules\Word
    {
        return $this->to;
    }
}
