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

        // Purge the table
        $conn = $em->getConnection();
        $truncate = $conn->prepare('TRUNCATE TABLE product_matches');
        $truncate->execute();

        // Find all products
        $productsQuery = $em->getRepository('IsicsOpenMiamMiamBundle:Product')
                            ->createQueryBuilder('p')
                            // ->setFirstResult(76)
                            // ->setMaxResults(8)
                            ->getquery();

        $allProducts = $productsQuery->getResult();

        // Fill the product_matches table
        foreach($allProducts as $product)
        {
            $id = $product->getId();

            $query = $em->getRepository('IsicsOpenMiamMiamBundle:SalesOrderRow');
            $subquery = $em->getRepository('IsicsOpenMiamMiamBundle:Product');

            $subQueryStmt = $subquery->createQueryBuilder('p')
                                     ->select('ctt.id')
                                     ->join('p.category', 'cat')
                                     ->join('IsicsOpenMiamMiamBundle:CategoryType', 'ctt', 'WITH', 'cat.categoryType = ctt.id')
                                     ->where('p.id = :productId')
                                     ->setParameter('productId', $id)
                                     ->getQuery()
                                     ->getResult();

            if (array_key_exists(0, $subQueryStmt)) 
            {
                $allProductsMatches = $query->createQueryBuilder('sor1')
                                            ->select( 'IDENTITY(sor1.product) as complementary_product', 'COUNT(sor1.id) as nb_common_orders')
                                            ->join('IsicsOpenMiamMiamBundle:SalesOrderRow', 'sor2', 'WITH', 'sor1.salesOrder = sor2.salesOrder')
                                            ->join('sor1.product', 'p')
                                            ->join('p.category', 'cat')
                                            ->join('IsicsOpenMiamMiamBundle:CategoryType', 'ctt', 'WITH', 'cat.categoryType = ctt.id')
                                            ->where('sor1.product != :productId')
                                            ->andWhere('sor2.product = :productId')
                                            ->andWhere('ctt.id ='. $subQueryStmt[0]['id'])
                                            ->groupBy('sor1.product')
                                            ->orderBy('nb_common_orders', 'DESC')
                                            ->setParameter('productId', $id)
                                            ->getQuery()->getResult();

                foreach($allProductsMatches as $match)
                {
                    $prodMatch = new ProductMatching();
                    $prodMatch->setProduct((int)$id);
                    $prodMatch->setComplementaryProduct((int)$match['complementary_product']);
                    $prodMatch->setNbCommonOrders((int)$match['nb_common_orders']);
                    $em->persist($prodMatch);
                    $em->flush();
                }
            }
        }
    }
}
