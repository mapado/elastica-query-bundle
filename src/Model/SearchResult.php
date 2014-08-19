<?php

namespace Mapado\ElasticaQueryBundle\Model;

use Elastica\ResultSet;

class SearchResult implements \Iterator, \Countable, \JsonSerializable
{
    /**
     * results
     *
     * @var \Iterator
     * @access private
     */
    private $results;

    /**
     * position
     *
     * @var int
     * @access private
     */
    private $position;

    /**
     * nextPage
     *
     * @var int
     * @access private
     */
    private $nextPage;

    /**
     * baseResults
     *
     * @var ResultSet
     * @access private
     */
    private $baseResults;

    /**
     * __construct
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->results = [];
        $this->position = 0;
    }

    /**
     * setResults
     *
     * @param \Iterator $results
     * @access public
     * @return SearchResult
     */
    public function setResults(\ArrayAccess $results)
    {
        $this->results = $results;

        return $this;
    }

    /**
     * Gets the value of baseResults
     *
     * @return ResultSet
     */
    public function getBaseResults()
    {
        return $this->baseResults;
    }

    /**
     * Sets the value of baseResults
     *
     * @param ResultSet $baseResults description
     *
     * @return SearchResult
     */
    public function setBaseResults(ResultSet $baseResults)
    {
        $this->baseResults = $baseResults;

        return $this;
    }

    /**
     * current
     *
     * @access public
     * @return mixed
     */
    public function current ()
    {
        return $this->results[$this->position];
    }

    /**
     * key
     *
     * @access public
     * @return int
     */
    public function key ()
    {
        return $this->position;
    }

    /**
     * next
     *
     * @access public
     * @return void
     */
    public function next ()
    {
        $this->position++;
    }

    /**
     * rewind
     *
     * @access public
     * @return void
     */
    public function rewind ()
    {
        $this->position = 0;
    }

    /**
     * valid
     *
     * @access public
     * @return boolean
     */
    public function valid ()
    {
        return isset($this->results[$this->position]);
    }

    /**
     * count
     *
     * @access public
     * @return int
     */
    public function count ()
    {
        return count($this->results);
    }

    /**
     * setNextPage
     *
     * @param int $page
     * @access public
     * @return SearchResult
     */
    public function setNextPage($page)
    {
        $this->nextPage = $page;

        return $this;
    }

    /**
     * getNextPage
     *
     * @access public
     * @return int
     */
    public function getNextPage()
    {
        return $this->nextPage;
    }

    public function jsonSerialize()
    {
        return $this->results;
    }

    /**
     * __call
     *
     * @param string $name method name
     * @param array $arguments arguments
     * @access public
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return call_user_func_array([$this->baseResults, $name], $arguments);
    }
}
