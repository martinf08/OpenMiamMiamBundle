<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Entity\Repository;
use Doctrine\ORM\EntityRepository;
use Isics\Bundle\OpenMiamMiamBundle\Entity\ProductMatching;

class ProductMatchingRepository extends EntityRepository
{
    /**
     * Truncate then fill product_matches table with products in common order for every product
     *
     * @param null
     * 
     * @return array
     */
    public function updateMatchingProducts() {

        $em = $this->getEntityManager();

        // Find all products
        $productsQuery = $em->getRepository('IsicsOpenMiamMiamBundle:Product')
                            ->createQueryBuilder('p')
                            ->orderBy('p.id')
                            ->getQuery();

        $allProducts = $productsQuery->getResult();

        // Fill the product_matches table
        foreach($allProducts as $product)
        {
            $id = $product->getId();

            // Delete previous entries
            $deleteMatches = $em->getRepository('IsicsOpenMiamMiamBundle:ProductMatching')
                                ->createQueryBuilder('pm')
                                ->delete(ProductMatching::class, 'pm')
                                ->where('pm.product = :productId')
                                ->setParameter('productId', $id)
                                ->getQuery()
                                ->execute();

            $query = $em->getRepository('IsicsOpenMiamMiamBundle:SalesOrderRow');

            $allProductsMatches = $query->createQueryBuilder('sor1')
                                        ->select('IDENTITY(sor1.product) as complementary_product', 'COUNT(sor1.id) as nb_common_orders')
                                        ->join('IsicsOpenMiamMiamBundle:SalesOrderRow', 'sor2', 'WITH', 'sor1.salesOrder = sor2.salesOrder')
                                        ->join('sor1.product', 'p1')
                                        ->join('p1.category', 'cat1')
                                        ->join('IsicsOpenMiamMiamBundle:CategoryType', 'ctt1', 'WITH', 'cat1.categoryType = ctt1.id')

                                        ->join('sor2.product', 'p2')
                                        ->join('p2.category', 'cat2')
                                        ->join('IsicsOpenMiamMiamBundle:CategoryType', 'ctt2', 'WITH', 'cat2.categoryType = ctt2.id')

                                        ->where('sor1.product != :productId')
                                        ->andWhere('sor2.product = :productId')

                                        ->andWhere('ctt1.id = ctt2.id')
                                        ->groupBy('sor1.product')
                                        ->addOrderBy('nb_common_orders', 'DESC')
                                        ->setParameter('productId', $id)
                                        ->getQuery()->getResult();

            foreach($allProductsMatches as $match)
            {
                $prodMatch = new ProductMatching();
                $prodMatch->setProduct((int)$id);
                $prodMatch->setComplementaryProduct((int)$match['complementary_product']);
                $prodMatch->setNbCommonOrders((int)$match['nb_common_orders']);
                $em->persist($prodMatch);
            }

            $em->flush();
        }
    }
}
