<?php

namespace Mapado\ElasticaQueryBundle;

use Doctrine\Common\EventManager;
use Elastica\Type;
use Mapado\ElasticaQueryBundle\Event\ObjectManagerEvent;
use Mapado\ElasticaQueryBundle\DataTransformer\DataTransformerInterface;
use Mapado\ElasticaQueryBundle\QueryBuilder;

class DocumentManager
{
    /**
     * type
     *
     * @var Type
     * @access private
     */
    private $type;

    /**
     * eventManager
     *
     * @var EventManager
     * @access private
     */
    private $eventManager;

    /**
     * dataTransformer
     *
     * @var DataTransformerInterface
     * @access private
     */
    private $dataTransformer;


    /**
     * __construct
     *
     * @param Type $type
     * @param EventManager $eventManager
     * @access public
     */
    public function __construct(Type $type, EventManager $eventManager)
    {
        $this->type = $type;
        $this->eventManager = $eventManager;
    }

    /**
     * setDataTransformer
     *
     * @param DataTransformerInterface $dataTransformer
     * @access public
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
     * @return \Mapado\FrontBundle\Search\ActivityQueryBuilder
     * @access public
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        return $this->handleQueryBuilder(new QueryBuilder($this));
    }

    /**
     * handleQueryBuilder
     *
     * @param QueryBuilder $eqb
     * @access public
     * @return QueryBuilder
     */
    public function handleQueryBuilder(QueryBuilder $eqb)
    {
        $this->eventManager->dispatchEvent('loadClassMetadata', new ObjectManagerEvent($this));

        if ($this->dataTransformer) {
            $eqb->setDataTransformer($this->dataTransformer);
        }
        return $eqb;
    }

    /**
     * getEventManager
     *
     * @access public
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
     * @access public
     * @return mixed
     */
    public function addEventListener($event, $listener)
    {
        return $this->eventManager->addEventListener($event, $listener);
    }

    /**
     * getElasticType
     *
     * @access public
     * @return Type
     */
    public function getElasticType()
    {
        return $this->type;
    }
}
