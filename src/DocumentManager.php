<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle;

use Doctrine\Common\EventManager;
use Elastica\ResultSet;
use Elastica\Type;
use Mapado\ElasticaQueryBundle\DataTransformer\DataTransformerInterface;
use Mapado\ElasticaQueryBundle\Event\ObjectEvent;
use Mapado\ElasticaQueryBundle\Event\ObjectManagerEvent;
use Mapado\ElasticaQueryBundle\Exception\NoMoreResultException;
use Mapado\ElasticaQueryBundle\Model\SearchResult;

class DocumentManager
{
    /**
     * type
     *
     * @var Type
     */
    private $type;

    /**
     * eventManager
     *
     * @var EventManager
     */
    private $eventManager;

    /**
     * dataTransformer
     *
     * @var DataTransformerInterface
     */
    private $dataTransformer;

    /**
     * queryBuilderClass
     *
     * @var string
     */
    private $queryBuilderClass;

    /**
     * __construct
     *
     * @param Type $type
     * @param EventManager $eventManager
     */
    public function __construct(Type $type, EventManager $eventManager, $queryBuilderClass = null)
    {
        $this->type = $type;
        $this->eventManager = $eventManager;
        $this->queryBuilderClass = $queryBuilderClass;
    }

    /**
     * setDataTransformer
     *
     * @param DataTransformerInterface $dataTransformer
     *
     * @return DocumentManager
     */
    public function setDataTransformer(DataTransformerInterface $dataTransformer)
    {
        $this->dataTransformer = $dataTransformer;

        return $this;
    }

    /**
     * createQueryBuilder
     *
     * @param Type $index
     *
     * @return \Mapado\FrontBundle\Search\ActivityQueryBuilder
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        if ($this->queryBuilderClass) {
            $queryBuilderClass = $this->queryBuilderClass;
            $queryBuilderInstance = new $queryBuilderClass($this);
            if (!$queryBuilderInstance instanceof QueryBuilder) {
                throw new \InvalidArgumentException(
                    '$queryBuilderClass must be an instance of \Mapado\ElasticaQueryBuilder\QueryBuilder'
                );
            }
        } else {
            $queryBuilderInstance = new QueryBuilder($this);
        }

        return $this->handleQueryBuilder($queryBuilderInstance);
    }

    /**
     * handleQueryBuilder
     *
     * @param QueryBuilder $eqb
     *
     * @return QueryBuilder
     */
    public function handleQueryBuilder(QueryBuilder $eqb)
    {
        $this->eventManager->dispatchEvent('loadClassMetadata', new ObjectManagerEvent($this));

        return $eqb;
    }

    /**
     * getEventManager
     *
     * @return EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * addEventListener passthrough set because I did not found how to record the tagged events
     * before the first "getEventManager" method is set
     *
     * @param mixed $event
     * @param mixed $listener
     *
     * @return mixed
     */
    public function addEventListener($event, $listener)
    {
        return $this->eventManager->addEventListener($event, $listener);
    }

    /**
     * getElasticType
     *
     * @return Type
     */
    public function getElasticType()
    {
        return $this->type;
    }

    public function handleResultSet(ResultSet $resultSet)
    {
        if (!$this->dataTransformer) {
            $results = $resultSet;
        } else {
            $count = count($resultSet);
            if ($count > 0) {
                $results = new \SplFixedArray($count);
                foreach ($resultSet as $i => $result) {
                    $item = $this->dataTransformer->transform($result);
                    $results[$i] = $item;

                    $this->getEventManager()
                        ->dispatchEvent('postLoad', new ObjectEvent($item));
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
     *
     * @return int
     */
    private function getNextPage(ResultSet $results)
    {
        $query = $results->getQuery();
        $from = $query->hasParam('from') ? $query->getParam('from') : 0;
        $size = $query->hasParam('size') ? $query->getParam('size') : 10;
        $hits = $results->getTotalHits();

        if (0 == count($results) && $from > 0) {
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
}
