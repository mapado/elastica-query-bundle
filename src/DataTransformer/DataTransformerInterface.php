<?php

namespace Mapado\ElasticaQueryBundle\DataTransformer;

use Elastica\Result;

interface DataTransformerInterface
{
    /**
     * transform
     *
     * @param Result $value
     * @access public
     * @return mixed
     */
    public function transform(Result $value);
}
