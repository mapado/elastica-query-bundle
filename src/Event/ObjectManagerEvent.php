<?php

namespace Mapado\ElasticaQueryBundle\Event;

use Doctrine\Common\EventArgs;

class ObjectManagerEvent extends EventArgs
{
    /**
     * objectManager
     *
     * @var object
     * @access private
     */
    private $objectManager;

    /**
     * __construct
     *
     * @param object $objectManager
     * @access public
     */
    public function __construct($objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * getObjectManager
     *
     * @access public
     * @return object
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
