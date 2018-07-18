<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle\Event;

use Doctrine\Common\EventArgs;

class ObjectManagerEvent extends EventArgs
{
    /**
     * objectManager
     *
     * @var object
     */
    private $objectManager;

    /**
     * __construct
     *
     * @param object $objectManager
     */
    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * getObjectManager
     *
     * @return object
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
