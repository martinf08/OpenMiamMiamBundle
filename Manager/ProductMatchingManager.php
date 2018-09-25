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
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

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
     * Update the list of matching products
     *
     * @param OutputInterface $output
     */
    public function updateMatchingProducts(OutputInterface $output)
    {
        $repository = $this->entityManager->getRepository(Product::class);
        $allProducts = $repository->findAll();

        $allProductsIndexes = $repository->allProductsIdIteration();
        $countAllProducts = count($allProducts);
        $progressBar = new ProgressBar($output, $countAllProducts);
        $progressBar->start();

	    $progressBar->setBarCharacter('<fg=green>•</>');
        $progressBar->setEmptyBarCharacter("<fg=red>•</>");
        $progressBar->setProgressCharacter("<fg=green>➤</>");
        $progressBar->setFormat(
            "%current%/%max% [%bar%] %percent:3s%%\n  Remaining : %estimated:-6s%"
        );
        $i = 1;
        foreach ($allProductsIndexes as $productIndex) {

            foreach ($productIndex as $index) {
                if ($i +1 == $countAllProducts) {
                    $progressBar->setCurrent($countAllProducts);
                    $progressBar->finish();
                }
                else {
                    $progressBar->setCurrent($i);
                }
                  $pmRepository = $repository = $this->entityManager->getRepository(ProductMatching::class);
                  $pmRepository->updateMatchingProducts($index['id']);
              }
              
            $i++;
        }
    }
}