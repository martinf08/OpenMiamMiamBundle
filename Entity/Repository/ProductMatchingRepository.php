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
     * Fill product_matches table with products in a common sale's order for every product
     *
     * @param null
     * 
     * @return array
     */
    public function updateMatchingProducts($id) {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();

        $deleteQuery = 'DELETE FROM product_matches WHERE product_id = :id';
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bindParam('id',$id);
        $stmt->execute();

        $insertQuery = 'INSERT INTO product_matches (product_id, matching_product_id, nb_common_orders)
        SELECT sor2.product_id AS `product_id`, sor1.product_id AS matching_product_id, COUNT(sor1.id) AS nb_common_orders
                  FROM sales_order_row AS sor1
                  JOIN sales_order_row AS sor2 ON (sor2.sales_order_id = sor1.sales_order_id)
                  JOIN product as p1 ON p1.id = sor1.product_id
                  JOIN category AS cat1 ON p1.category_id = cat1.id
                  JOIN category_type AS ctt1 ON cat1.category_type_id = ctt1.id
                  
                  JOIN product as p2 ON p2.id = sor2.product_id
                  JOIN category AS cat2 ON p2.category_id = cat2.id
                  JOIN category_type AS ctt2 ON cat2.category_type_id = ctt2.id
                  
                  WHERE sor1.product_id != sor2.product_id
                  AND sor2.product_id = :id
                  AND ctt1.id = ctt2.id
                  GROUP BY matching_product_id, `product_id`
                  ORDER BY `product_id`, nb_common_orders DESC';
        $stmt2 = $conn->prepare($insertQuery);
        $stmt2->bindParam('id',$id );
        $stmt2->execute();
    }
}
