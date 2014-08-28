<?php

namespace Mapado\ElasticaQueryBundle;

use \Elastica\Aggregation\AbstractAggregation;
use \Elastica\Filter;
use \Elastica\Filter\AbstractFilter;
use \Elastica\Query\AbstractQuery;
use \Elastica\Query;
use \Elastica\ResultSet;
use \Elastica\Type;

use Mapado\ElasticaQueryBundle\DataTransformer\DataTransformerInterface;
use Mapado\ElasticaQueryBundle\Exception\NoMoreResultException;
use Mapado\ElasticaQueryBundle\Model\SearchResult;

class QueryBuilder
{
    /**
     * type
     *
     * @var Type
     * @access private
     */
    private $type;

    /**
     * filterList
     *
     * @var array
     * @access private
     */
    private $filterList;

    /**
     * queryList
     *
     * @var array
     * @access private
     */
    private $queryList;

    /**
     * aggregationList
     *
     * @var array
     * @access private
     */
    private $aggregationList;

    /**
     * firstResults
     *
     * @var int
     * @access private
     */
    private $firstResults;

    /**
     * maxResults
     *
     * @var int
     * @access private
     */
    private $maxResults;

    /**
     * dataTransformer
     *
     * @var mixed
     * @access private
     */
    private $dataTransformer;

    /**
     * __construct
     *
     * @param Type $type
     * @access public
     */
    public function __construct(Type $type)
    {
        $this->type = $type;
        $this->filterList = [];
        $this->queryList = [];
    }

    /**
     * setDataTransformer
     *
     * @param DataTransformerInterface $dataTransformer
     * @access public
     * @return QueryBuilder
     */
    public function setDataTransformer(DataTransformerInterface $dataTransformer)
    {
        $this->dataTransformer = $dataTransformer;
        return $this;
    }

    /**
     * addFilter
     *
     * @param AbstractFilter $filter
     * @access public
     * @return QueryBuilder
     */
    public function addFilter(AbstractFilter $filter)
    {
        $this->filterList[] = $filter;
        return $this;
    }

    /**
     * addQuery
     *
     * @param AbstractQuery $query
     * @access public
     * @return QueryBuilder
     */
    public function addQuery(AbstractQuery $query)
    {
        $this->queryList[] = $query;
        return $this;
    }

    /**
     * addAggregation
     *
     * @param AbstractAggregation $aggregation
     * @access public
     * @return QueryBuilder
     */
    public function addAggregation(AbstractAggregation $aggregation)
    {
        $this->aggregationList[] = $aggregation;
        return $this;
    }

    /**
     * setMaxResults
     *
     * @param mixed $maxResults
     * @access public
     * @return QueryBuilder
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
        return $this;
    }

    /**
     * setFirstResults
     *
     * @param int $firstResults
     * @access public
     * @return QueryBuilder
     */
    public function setFirstResults($firstResults)
    {
        $this->firstResults = $firstResults;
        return $this;
    }

    /**
     * getResult
     *
     * @access public
     * @return Elastica\ResultSet
     */
    public function getResult()
    {
        if ($this->filterList) {
            $filteredQuery = new Query\Filtered($this->getQuery(), $this->getFilter());
            $query = Query::create($filteredQuery);
        } else {
            $query = $this->getQuery();
        }

        // manage size / from
        if ($this->firstResults) {
            $query->setFrom($this->firstResults);
        }
        if ($this->maxResults) {
            $query->setSize($this->maxResults);
        }

        if (!empty($this->aggregationList)) {
            foreach ($this->aggregationList as $aggregation) {
                $query->addAggregation($aggregation);
            }
        }

        return $this->execute($query);
    }

    /**
     * getQuery
     *
     * @access private
     * @return void
     */
    private function getQuery()
    {
        if (!$this->queryList) {
            return null;
        }

        if (count($this->queryList) == 1) {
            return current($this->queryList);
        }

        $query = new Query\Bool();
        foreach ($this->queryList as $tmpQuery) {
            $query->addMust($tmpQuery);
        }

        return $query;
    }

    /**
     * getFilter
     *
     * @access private
     * @return AbstractFilter
     */
    private function getFilter()
    {
        if (!$this->filterList) {
            return null;
        }

        if (count($this->filterList) == 1) {
            return current($this->filterList);
        }


        $boolFilters = [];
        $andFilters = [];
        foreach ($this->filterList as $tmpFilter) {
            if ($this->isAndFilter($tmpFilter)) {
                $andFilters[] = $tmpFilter;
            } else {
                $boolFilters[] = $tmpFilter;
            }
        }

        $boolFilter = null;
        $nbBoolFilters = count($boolFilters);
        if ($nbBoolFilters > 1) {
            $boolFilter = new Filter\Bool();
            foreach ($boolFilters as $tmpFilter) {
                $boolFilter->addMust($tmpFilter);
            }

            array_unshift($andFilters, $boolFilter);
        } elseif ($nbBoolFilters == 1) {
            $andFilters = array_merge($boolFilters, $andFilters);
        }

        $nbAndFilters = count($andFilters);
        if ($nbAndFilters == 1) {
            return current($andFilters);
        } elseif ($nbAndFilters > 1) {
            $filter = new Filter\BoolAnd();
            $filter->setFilters($andFilters);
            return $filter;
        }

        return null;
    }

    /**
     * execute
     *
     * @param string|array|\Elastica\Query $query Array with all query data inside or a Elastica\Query object
     * @access private
     * @return \Iterator
     */
    private function execute($query)
    {
        $resultSet = $this->type->search($query);

        if (!$this->dataTransformer) {
            $results = $resultSet;
        } else {
            $count = count($resultSet);
            if ($count > 0) {
                $results = new \SplFixedArray($count);
                foreach ($resultSet as $i => $result) {
                    $item = $this->dataTransformer->transform($result);
                    $results[$i] = $item;
                }
            } else {
                $results = new \SplFixedArray(0);
            }
        }

        // Generate the result object
        $nextPage = $this->getNextPage($resultSet);

        $searchResults = new SearchResult();
        $searchResults->setResults($results)
            ->setNextPage($nextPage)
            ->setBaseResults($resultSet);

        return $searchResults;
    }

    /**
     * getNextPage
     *
     * @param ResultSet $results
     * @param int $from
     * @access private
     * @return int
     */
    private function getNextPage(ResultSet $results)
    {
        $query = $results->getQuery();
        $from = $query->hasParam('from') ? $query->getParam('from') : 0;
        $size = $query->hasParam('size') ? $query->getParam('size') : 10;
        $hits = $results->getTotalHits();

        if (count($results) == 0 && $from > 0) {
            $msg = 'current page is higher than max page';
            throw new NoMoreResultException($msg);
        } elseif ($hits > $from + $size) {
            if ($size > 0) {
                $nextPage = ((int) $from / $size) + 2;
            } else {
                $nextPage = 2;
            }
        } else {
            $nextPage = null;
        }


        return $nextPage;
    }

    /**
     * select if the filter is more in a `BoolAnd` or a `Bool`.
     * @see http://www.elasticsearch.org/blog/all-about-elasticsearch-filter-bitsets/
     *
     * @param Filter\AbstractFilter $filter
     * @access private
     * @return void
     */
    private function isAndFilter(Filter\AbstractFilter $filter)
    {
        $filterName = substr(get_class($filter), 16);

        return $filterName === 'Script'
            || $filterName === 'NumericRange'
            ||  substr($filterName, 0, 3) === 'Geo'
        ;
    }
}
