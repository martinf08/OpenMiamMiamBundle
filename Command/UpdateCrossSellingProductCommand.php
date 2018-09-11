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


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

Class UpdateCrossSellingProductCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('app:update-cross-selling')

            ->addArgument('message', InputArgument::REQUIRED, 'The message')

            ->setDescription('Update the cross selling product list')

            ->setHelp('This command is used for update the cross selling list');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Hello World',
            '============'

        ]);

        $repository = $this->getContainer()->get('doctrine')
            ->getEntityManager()
            ->getRepository("IsicsOpenMiamMiamBundle:Product")
            ->FindOneByMessage($input->getArgument('message'));


        $output->writeln('Message : ' . $input->getArgument('message'));

    }
}
