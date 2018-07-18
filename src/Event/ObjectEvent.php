<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle\Event;

use Doctrine\Common\EventArgs;

/**
 * ObjectEvent
 *
 * @author Julien Deniau <julien.deniau@mapado.com>
 */
class ObjectEvent extends EventArgs
{
    /**
     * object
     *
     * @var object
     */
    private $object;

    /**
     * __construct
     *
     * @param object $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    /**
     * getObject
     *
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }
}
