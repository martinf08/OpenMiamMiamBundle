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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Model\Cart;
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
     * Find matching products in cart
     * 
     * @param Cart $cart
     * @param Branch $branch
     * 
     * @return array
     */
    public function findMatchingProductsForCart($cart, Branch $branch)
    {
        $productMatchingRepository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:ProductMatching');
        $productRepository = $this->entityManager->getRepository('IsicsOpenMiamMiamBundle:Product');

        $listOfProductMatchingByCartItems = array();
        $listOfIdInCart = array();
        foreach ($cart->getItems() as $item) {
            $listOfProductMatchingByCartItems = array_merge($listOfProductMatchingByCartItems, $productMatchingRepository->findMatchingProducts($item->getProduct(), $branch));
            array_push($listOfIdInCart, $item->getProductId());
        }

        foreach ($listOfProductMatchingByCartItems as $key => $item) {
            foreach ($listOfIdInCart as  $id) {
                if ($id  == $item->getId())
                    unset($listOfProductMatchingByCartItems[$key]);
            }
        }

        $productsMatching = array();
        foreach ($listOfProductMatchingByCartItems as $item)
            array_push($productsMatching, $item->getId());

        $countProducts = array_count_values($productsMatching);
        arsort($countProducts);

        $filteredMatches = array();

        foreach ($countProducts as $id => $value)
            array_push($filteredMatches, $productRepository->findOneByIdAndVisibleInBranch($id, $branch));

        return array_slice($filteredMatches, 0, 3);
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

