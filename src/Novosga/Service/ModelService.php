<?php

namespace Novosga\Service;

use Doctrine\ORM\EntityManager;

/**
 * ModelService.
 *
 * 
 */
abstract class ModelService
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
}
