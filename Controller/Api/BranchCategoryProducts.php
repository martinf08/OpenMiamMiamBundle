<?php
// api/src/Controller/BookSpecial.php

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Api;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BranchCategoryProducts extends Controller
{
    public function __invoke($id,$idCat)
    {
        return $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Product')->findAllVisibleInBranchAndCategory(
            $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Branch')->find($id),
            $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Category')->find($idCat)
         );;
    }
}