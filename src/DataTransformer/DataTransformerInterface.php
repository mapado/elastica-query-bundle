<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle\DataTransformer;

use Elastica\Result;

interface DataTransformerInterface
{
    /**
     * transform
     *
     * @param Result $value
     *
     * @return mixed
     */
    public function transform(Result $value);
}
