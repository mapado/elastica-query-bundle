<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Filter;
use Elastica\Filter\AbstractFilter;
use Elastica\Query\AbstractQuery;
use Elastica\Query as ElasticaQuery;

class QueryBuilder
{
    /**
     * documentManager
     *
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * filterList
     *
     * @var array
     */
    private $filterList;

    /**
     * queryList
     *
     * @var array
     */
    private $queryList;

    /**
     * sortList
     *
     * @var array
     */
    private $sortList;

    /**
     * aggregationList
     *
     * @var array
     */
    private $aggregationList;

    /**
     * firstResults
     *
     * @var int
     */
    private $firstResults;

    /**
     * maxResults
     *
     * @var int
     */
    private $maxResults;

    /**
     * minScore
     *
     * @var int
     */
    private $minScore;

    /**
     * __construct
     *
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
        $this->filterList = [];
        $this->queryList = [];
        $this->sortList = [];
    }

    /**
     * addFilter
     *
     * @param AbstractFilter $filter
     *
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
     *
     * @return QueryBuilder
     */
    public function addQuery(AbstractQuery $query)
    {
        $this->queryList[] = $query;

        return $this;
    }

    /**
     * addSort
     *
     * @param mixed $sort Sort parameter
     *
     * @return QueryBuilder
     */
    public function addSort($sort)
    {
        $this->sortList[] = $sort;

        return $this;
    }

    /**
     * addAggregation
     *
     * @param AbstractAggregation $aggregation
     *
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
     *
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
     *
     * @return QueryBuilder
     */
    public function setFirstResults($firstResults)
    {
        $this->firstResults = $firstResults;

        return $this;
    }

    /**
     * setMinScore
     *
     * @return QueryBuilder
     */
    public function setMinScore($minScore)
    {
        $this->minScore = $minScore;

        return $this;
    }

    /**
     * getElasticQuery
     *
     * @return Query
     */
    public function getElasticQuery()
    {
        if ($this->filterList) {
            $filteredQuery = new ElasticaQuery\Filtered($this->getQuery(), $this->getFilter());
            $query = new Query($filteredQuery);
        } else {
            $query = new Query($this->getQuery());
        }
        $query->setDocumentManager($this->documentManager);

        // manage size / from
        if ($this->firstResults) {
            $query->setFrom($this->firstResults);
        }
        if (isset($this->maxResults)) {
            $query->setSize($this->maxResults);
        }

        if (!empty($this->sortList)) {
            $query->setSort($this->sortList);
        }

        if (isset($this->minScore)) {
            $query->setMinScore($this->minScore);
        }

        if (!empty($this->aggregationList)) {
            foreach ($this->aggregationList as $aggregation) {
                $query->addAggregation($aggregation);
            }
        }

        return $query;
    }

    /**
     * getResult
     *
     * @return Elastica\ResultSet
     */
    public function getResult()
    {
        return $this->getElasticQuery()->getResult();
    }

    /**
     * getQuery
     *
     * @return \Elastica\Query\AbstractQuery
     */
    private function getQuery()
    {
        if (!$this->queryList) {
            return;
        }

        if (1 == count($this->queryList)) {
            return current($this->queryList);
        }

        $query = new ElasticaQuery\BoolQuery();
        foreach ($this->queryList as $tmpQuery) {
            $query->addMust($tmpQuery);
        }

        return $query;
    }

    /**
     * getFilter
     *
     * @return AbstractFilter
     */
    private function getFilter()
    {
        if (!$this->filterList) {
            return;
        }

        if (1 == count($this->filterList)) {
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
            $boolFilter = new Filter\BoolFilter();
            foreach ($boolFilters as $tmpFilter) {
                $boolFilter->addMust($tmpFilter);
            }

            array_unshift($andFilters, $boolFilter);
        } elseif (1 == $nbBoolFilters) {
            $andFilters = array_merge($boolFilters, $andFilters);
        }

        $nbAndFilters = count($andFilters);
        if (1 == $nbAndFilters) {
            return current($andFilters);
        } elseif ($nbAndFilters > 1) {
            $filter = new Filter\BoolAnd();
            $filter->setFilters($andFilters);

            return $filter;
        }

        return;
    }

    /**
     * select if the filter is more in a `BoolAnd` or a `BoolFilter`.
     *
     * @see http://www.elasticsearch.org/blog/all-about-elasticsearch-filter-bitsets/
     *
     * @param Filter\AbstractFilter $filter
     */
    private function isAndFilter(Filter\AbstractFilter $filter)
    {
        $filterName = substr(get_class($filter), 16);

        return 'Script' === $filterName
            || 'NumericRange' === $filterName
            || 'Geo' === substr($filterName, 0, 3)
        ;
    }
}
