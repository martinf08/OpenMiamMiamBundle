<?php
// api/src/Controller/BookSpecial.php

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Api;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BranchOccurences extends Controller
{
    public function __invoke(Branch $branch)
    {
        $branchOccurrenceManager = $this->container->get('open_miam_miam.branch_occurrence_manager');

        return [
            'nextBranchOccurrence' => $branchOccurrenceManager->getNext($branch),
            'closingDateTime' => $branchOccurrenceManager->getClosingDateTime($branch),
            'openingDateTime' => $branchOccurrenceManager->getOpeningDateTime($branch)
        ];
    }
}