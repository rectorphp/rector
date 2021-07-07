<?php

namespace Rector\Tests\DowngradePhp70\Rector\String_\DowngradeGeneratedScalarTypesRector\Fixture;

$code = <<<'EOF'
    /**
     * @return array|bool|float|int|string|null
     */
    public function getParameter(string $name)
    {
        if (isset($this->buildParameters[$name])) {
            return $this->buildParameters[$name];
        }
        if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || \array_key_exists($name, $this->parameters))) {
            throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }
        if (isset($this->loadedDynamicParameters[$name])) {
            return $this->loadedDynamicParameters[$name] ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
        }
        return $this->parameters[$name];
    }
    public function hasParameter(string $name): bool
    {
        if (isset($this->buildParameters[$name])) {
            return true;
        }
        return isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || \array_key_exists($name, $this->parameters);
    }
    public function setParameter(string $name, $value): void
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }
    public function getParameterBag(): ParameterBagInterface
    {
        if (null === $this->parameterBag) {
            $parameters = $this->parameters;
            foreach ($this->loadedDynamicParameters as $name => $loaded) {
                $parameters[$name] = $loaded ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
            }
            foreach ($this->buildParameters as $name => $value) {
                $parameters[$name] = $value;
            }
            $this->parameterBag = new FrozenParameterBag($parameters);
        }
        return $this->parameterBag;
    }
EOF;

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\String_\DowngradeGeneratedScalarTypesRector\Fixture;

$code = <<<'EOF'
/**
 * @return array|bool|float|int|string|null
 * @param string $name
 */
public function getParameter($name)
{
    $name = (string) $name;
    if (isset($this->buildParameters[$name])) {
        return $this->buildParameters[$name];
    }
    if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || \array_key_exists($name, $this->parameters))) {
        throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
    }
    if (isset($this->loadedDynamicParameters[$name])) {
        return $this->loadedDynamicParameters[$name] ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
    }
    return $this->parameters[$name];
}
/**
 * @param string $name
 * @return bool
 */
public function hasParameter($name)
{
    $name = (string) $name;
    if (isset($this->buildParameters[$name])) {
        return true;
    }
    return isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || \array_key_exists($name, $this->parameters);
}
/**
 * @param string $name
 * @return void
 */
public function setParameter($name, $value)
{
    $name = (string) $name;
    throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
}
public function getParameterBag()
{
    if (null === $this->parameterBag) {
        $parameters = $this->parameters;
        foreach ($this->loadedDynamicParameters as $name => $loaded) {
            $parameters[$name] = $loaded ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
        }
        foreach ($this->buildParameters as $name => $value) {
            $parameters[$name] = $value;
        }
        $this->parameterBag = new FrozenParameterBag($parameters);
    }
    return $this->parameterBag;
}

EOF;

?>
