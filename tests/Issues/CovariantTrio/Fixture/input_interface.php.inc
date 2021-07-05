<?php

class ArgvInput extends Input
{
    /**
     * Parses an argument.
     *
     * @throws RuntimeException When too many arguments are given
     */
    private function parseArgument(string $token, array $all)
    {
        if (($inputArgument = $all[$key = array_key_first($all)] ?? null) && 'command' === $inputArgument->getName()) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameterOption($values, bool $onlyParams = false)
    {
    }
}

abstract class Input implements InputInterface
{

}

class ArrayInput extends Input
{
    public function hasParameterOption($values, bool $onlyParams = false)
    {

    }
}

interface InputInterface
{
    public function hasParameterOption($values, bool $onlyParams = false);
}

?>
-----
<?php

class ArgvInput extends Input
{
    /**
     * Parses an argument.
     *
     * @throws RuntimeException When too many arguments are given
     * @param string $token
     */
    private function parseArgument($token, array $all)
    {
        $token = (string) $token;
        reset($all);
        if (($inputArgument = isset($all[$key = key($all)]) ? $all[$key = key($all)] : null) && 'command' === $inputArgument->getName()) {
        }
    }

    /**
     * {@inheritdoc}
     * @param bool $onlyParams
     */
    public function hasParameterOption($values, $onlyParams = false)
    {
    }
}

abstract class Input implements InputInterface
{

}

class ArrayInput extends Input
{
    /**
     * @param bool $onlyParams
     */
    public function hasParameterOption($values, $onlyParams = false)
    {

    }
}

interface InputInterface
{
    /**
     * @param bool $onlyParams
     */
    public function hasParameterOption($values, $onlyParams = false);
}

?>
