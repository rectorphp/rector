<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

/**
 * @see \Clue\React\NDJson\Decoder
 */
final class ClueJsonDecode
{
    private $assoc;
    private $depth;
    private $options;

    public function run($data)
    {
        if ($this->options === 0) {
            $data = \json_decode($data, $this->assoc, $this->depth);
        } else {
            $data = \json_decode($data, $this->assoc, $this->depth, $this->options);
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

/**
 * @see \Clue\React\NDJson\Decoder
 */
final class ClueJsonDecode
{
    private $assoc;
    private $depth;
    private $options;

    public function run($data)
    {
        if ($this->options === 0) {
            $data = \json_decode($data, $this->assoc === null ?: $this->assoc, $this->depth);
        } else {
            $data = \json_decode($data, $this->assoc === null ?: $this->assoc, $this->depth, $this->options);
        }
    }
}

?>
