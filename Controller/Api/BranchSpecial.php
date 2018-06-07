<?php
// api/src/Controller/BookSpecial.php

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Api;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BranchSpecial extends Controller
{


    public function __invoke(Branch $data)
    {
        $categories = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Category')
        ->findLevel1WithProductsInBranch($data);
        return $categories;
    }
}