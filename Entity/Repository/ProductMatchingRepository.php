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
     * Returns a complete list of the most sold products' id for each product
     *
     * @param null
     * 
     * @return array
     */
    public function updateMatchingProducts() {

        $conn = $this->getEntityManager()->getConnection();
        
        // purge the table
        $trunc = $conn->prepare('TRUNCATE TABLE product_matches');
        $trunc->execute();

        // Find all products
        $allProducts = 'SELECT product.id FROM product ORDER BY product.id';
        $stmt1 = $conn->prepare($allProducts);
        $stmt1->execute();
        $arrProds = $stmt1->fetchAll();

        $prodList = array();
        foreach($arrProds as $prodId) {
            $productsInOrders = '
                SELECT sor1.product_id, COUNT(sor1.id) AS frequency
                FROM sales_order_row AS sor1
                JOIN sales_order_row AS sor2 ON (sor2.sales_order_id = sor1.sales_order_id AND sor2.product_id = :id)
                JOIN product as p ON p.id = sor1.product_id
                JOIN category AS cat ON p.category_id = cat.id
                JOIN category_type AS ctt ON cat.category_type_id = ctt.id
                WHERE sor1.product_id != :id
                AND ctt.id = (SELECT category_type.id
                              FROM product as p
                              JOIN category ON p.category_id = category.id
                              JOIN category_type ON category.category_type_id = category_type.id
                              WHERE p.id = :id)
                GROUP BY 1
                ORDER BY 2 DESC';
            $stmt2 = $conn->prepare($productsInOrders);
            $stmt2->bindParam(':id', $prodId['id']);
            $stmt2->execute();

            $allProductsItems = array();
            foreach ($stmt2->fetchAll() as $productList) {
                array_push($allProductsItems, $productList['product_id']);
            }

            $prodList[$prodId['id']] = $allProductsItems;
            
            $prodMatch = new ProductMatching();
            $prodMatch->setProduct((int)$prodId['id']);

            switch (count($allProductsItems))
            {
                case 1:
                    $prodMatch->setfirstMatchProduct((int)$allProductsItems[0]);
                    break;
                case 2:
                    $prodMatch->setfirstMatchProduct((int)$allProductsItems[0]);
                    $prodMatch->setSecondMatchProduct((int)$allProductsItems[1]);
                    break;
                case 3:
                    $prodMatch->setfirstMatchProduct((int)$allProductsItems[0]);
                    $prodMatch->setSecondMatchProduct((int)$allProductsItems[1]);
                    $prodMatch->setThirdMatchProduct((int)$allProductsItems[2]);
                    break;
                default:
                    break;
            }
            
            $em = $this->getEntityManager();
            $em->persist($prodMatch);
            $em->flush();
        }
    }
}
