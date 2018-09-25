<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

use Isics\Bundle\OpenMiamMiamBundle\Manager\ProductMatchingManager;

class UpdateMatchingProductsCommand extends ContainerAwareCommand
{
    /**
     * @see ContainerAwareCommand
     */
    protected function configure()
    {
        $this->setName('openmiammiam:update-matching-products')
             ->setDescription('Fill table with the most sold product in same orders for every given products');
    }

    /**
     * @see ContainerAwareCommand
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Computing matching products...</comment>');
        $output->writeln('');
        $progressBar = new ProgressBar($output);
        $callback = function($i, $countAllProducts) use ($progressBar) {
            if ($i == 1) {
                $progressBar->start($countAllProducts);
                $progressBar->setBarCharacter('<fg=green>•</>');
                $progressBar->setEmptyBarCharacter("<fg=red>•</>");
                $progressBar->setProgressCharacter("<fg=green>➤</>");
                $progressBar->setFormat(
                    "%current%/%max% [%bar%] %percent:3s%%\n Elapsed : %elapsed% Remaining : %remaining:-6s%"
                );
            }
            else {
                $progressBar->setCurrent($i);
            }
        };

        $productMatchingManager = $this->getContainer()->get('open_miam_miam.product_matching_manager');
        $productMatchingManager->updateMatchingProducts($callback);

        $output->writeln('');
        $output->writeln('<info>Task is completed.</info>');
    }
}

