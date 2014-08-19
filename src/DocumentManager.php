<?php

namespace Mapado\ElasticaQueryBundle;

use Elastica\Type;
use Mapado\ElasticaQueryBundle\QueryBuilder;
use Mapado\ElasticaQueryBundle\DataTransformer\DataTransformerInterface;

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
     * __construct
     *
     * @param Type $type
     * @access public
     */
    public function __construct(Type $type)
    {
        $this->type = $type;
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
        $eqb = new QueryBuilder($this->type);
        if ($this->dataTransformer) {
            $eqb->setDataTransformer($this->dataTransformer);
        }
        return $eqb;
    }
}
