<?php
// api/src/Controller/BookSpecial.php

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Api\SalesOrder;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PostOrder extends Controller
{
    public function __invoke($obj)
    {
        //SalesOrderConfirmationType
        $orderManager = $this->get('open_miam_miam.sales_order_manager');
        /*
                $order = $orderManager->processSalesOrderFromCart(
                    $cart,
                    $branchOccurrence,
                    $user,
                    $form->getData()
                );

                $this->get('open_miam_miam.payment_manager')->computeConsumerCredit(
                    $order->getBranchOccurrence()->getBranch()->getAssociation(),
                    $order->getUser()
                );

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.sales_order.confirm_creation',
                    array('branchSlug' => $branch->getSlug(), 'id' => $order->getId())
                ));
         */
        dump($obj);
        return ["yay" => "yay"];
    }
}