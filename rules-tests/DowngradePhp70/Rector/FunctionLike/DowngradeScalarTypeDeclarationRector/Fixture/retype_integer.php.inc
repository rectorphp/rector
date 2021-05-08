<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

// @see https://3v4l.org/vS8MK
final class RetypeInteger
{
    public function run(int $number, bool $maybe, float $amount)
    {
        if ($maybe) {
            return $number;
        } else {
            return $amount;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

// @see https://3v4l.org/vS8MK
final class RetypeInteger
{
    /**
     * @param int $number
     * @param bool $maybe
     * @param float $amount
     */
    public function run($number, $maybe, $amount)
    {
        $number = (int) $number;
        $maybe = (bool) $maybe;
        $amount = (double) $amount;
        if ($maybe) {
            return $number;
        } else {
            return $amount;
        }
    }
}

?>
