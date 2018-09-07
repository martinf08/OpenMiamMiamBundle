<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Super;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
//CODE TEST, NE PAS COMMIT
use Doctrine\ORM\QueryBuilder;
//END CODE TEST

class GeneralController extends Controller
{
    /**
     * Show Dashboard
     *
     * @return Response
     */
    public function showDashboardAction()
    {
        //CODE TEST, NE PAS COMMIT
            $conn = $this->getDoctrine()->getEntityManager()->getConnection();

            $sql = '
                SELECT sales_order_id, GROUP_CONCAT(product.id) as products, MAX(category_type.id) as type
                FROM sales_order_row
                JOIN product ON sales_order_row.product_id = product.id
                INNER JOIN category ON product.category_id = category.id
                INNER JOIN category_type ON category.category_type_id = category_type.id
                GROUP BY sales_order_row.sales_order_id, category_type.id
                HAVING COUNT(category.category_type_id) > 1
                ORDER BY type
                ';
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            // returns an array of arrays (i.e. a raw data set)
            $results = $stmt->fetchAll();
        //END CODE TEST
        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super:showDashboard.html.twig', array('results' => $results));
    }
}
