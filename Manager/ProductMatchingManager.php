<?php
/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Manager;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Entity\ProductMatching;
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
     *
     * @param \Closure $callback
     */
    public function updateMatchingProducts(\Closure $callback)
    {
        $repository = $this->entityManager->getRepository(Product::class);
        $allProducts = $repository->findAll();
        $countAllProducts = count($allProducts);
        $allProductsIndexes = $repository->allProductsIdIteration();

        $i = 1;
        foreach ($allProductsIndexes as $productIndex) {
            if ($callback) {
                $callback($i, $countAllProducts);

                foreach ($productIndex as $index) {
                    $pmRepository = $repository = $this->entityManager->getRepository(ProductMatching::class);
                    $pmRepository->updateMatchingProducts($index['id']);
                }
                $i++;
            }
        }
    }
}

