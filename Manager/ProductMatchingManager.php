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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;
use Isics\Bundle\OpenMiamMiamBundle\Entity\ProductMatching;
use Isics\Bundle\OpenMiamMiamBundle\Model\Cart\Cart;
use Doctrine\ORM\EntityManager;

/**
 * Class ProductMatchingRepository
 *
 * @package Isics\Bundle\OpenMiamMiamBundle\Manager
 */
class ProductMatchingManager
{
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
        $this->entityManager = $entityManager;
    }

    /**
     * Find matching products
     * 
     * @param Product $product
     * @param Branch $branch
     * @param Cart $cart
     * 
     * @return array
     */
    public function findMatchingProducts(Product $product, Branch $branch, Cart $cart)
    {
        $productsInCart = array();
        foreach ($cart->getItems() as $item) {
            array_push($productsInCart, $item->getProduct()->getId());
        }

        if (empty($productsInCart)) {
            $productsInCart = 0; 
        }

        return $this->entityManager->getRepository(ProductMatching::class)->findMatchingProducts($product, $branch, $productsInCart);
    }

    /**
     * Update the list of matching products
     *
     * @param \Closure $callback
     */
    public function updateMatchingProducts(\Closure $callback = null)
    {
        $repository = $this->entityManager->getRepository(Product::class);
        $nbProducts = $repository->count();
        $allIds = $repository->findAllId();

        $i = 1;

        foreach ($allIds as $id) {
            $pmRepository = $this->entityManager->getRepository(ProductMatching::class);
            $pmRepository->updateMatchingProducts($id[0]['id']);

            if ($callback) {
                $callback($i, $nbProducts);
            }

            $i++;
        }
    }
}

