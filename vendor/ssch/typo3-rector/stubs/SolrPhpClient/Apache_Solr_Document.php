<?php

namespace RectorPrefix20211020;

if (\class_exists('Apache_Solr_Document')) {
    return;
}
class Apache_Solr_Document implements \IteratorAggregate
{
    /**
     * Document boost value
     *
     * @var float
     */
    protected $_documentBoost = \false;
    /**
     * Document field values, indexed by name
     *
     * @var array
     */
    protected $_fields = array();
    /**
     * Document field boost values, indexed by name
     *
     * @var array array of floats
     */
    protected $_fieldBoosts = array();
    /**
     * Clear all boosts and fields from this document
     */
    public function clear()
    {
        $this->_documentBoost = \false;
        $this->_fields = array();
        $this->_fieldBoosts = array();
    }
    /**
     * Get current document boost
     *
     * @return mixed will be false for default, or else a float
     */
    public function getBoost()
    {
        return $this->_documentBoost;
    }
    /**
     * Set document boost factor
     *
     * @param mixed $boost Use false for default boost, else cast to float that should be > 0 or will be treated as false
     */
    public function setBoost($boost)
    {
        $boost = (float) $boost;
        if ($boost > 0.0) {
            $this->_documentBoost = $boost;
        } else {
            $this->_documentBoost = \false;
        }
    }
    public function setMultiValue($key, $value, $boost = \false)
    {
        $this->addField($key, $value, $boost);
    }
    public function addField($key, $value, $boost = \false)
    {
    }
    public function getFieldBoost($key)
    {
    }
    public function setFieldBoost($key, $boost)
    {
    }
    public function getField($key)
    {
    }
    /**
     * @return mixed[]
     */
    public function getFields()
    {
        return [];
    }
    /**
     * @return mixed[]
     */
    public function getFieldBoosts()
    {
        return [];
    }
    /**
     * @return mixed[]
     */
    public function getFieldNames()
    {
        return [];
    }
    /**
     * @return mixed[]
     */
    public function getFieldValues()
    {
        return [];
    }
    public function getIterator()
    {
        $arrayObject = new \ArrayObject([]);
        return $arrayObject->getIterator();
    }
    public function __get($key)
    {
    }
    public function __set($key, $value)
    {
    }
    public function setField($key, $value, $boost = \false)
    {
    }
    public function __isset($key)
    {
    }
    public function __unset($key)
    {
    }
    public function __call($name, $arguments)
    {
    }
}
\class_alias('Apache_Solr_Document', 'Apache_Solr_Document', \false);
