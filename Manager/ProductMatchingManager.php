<?php
/**
 * Created by PhpStorm.
 * User: linux
 * Date: 24/09/18
 * Time: 13:28
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Doctrine\ORM\EntityManager;

/**
 * Class ProductMatchingRepository
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class ProductMatchingManager {

    /**
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Constructs object
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager  = $entityManager;
    }

    /**
     * Update the list of matching products
     */
    public function updateMatchingProducts() {
        $query = $this->entiyManager->createQuery('SELECT p FROM IsicsOpenmiamMiamBundle:Product');
       // $iterable =  $query->iterate();
    }
}