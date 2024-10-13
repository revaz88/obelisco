<?php

namespace Novosga\Api;

use Doctrine\ORM\EntityManager;

/**
 * Api.
 *
 * 
 */
abstract class Api
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
}
