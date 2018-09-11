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
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Isics\Bundle\OpenMiamMiamBundle\Entity\BranchOccurrence;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Product;

class ProductRepository extends EntityRepository
{
    /**
     * Finds products visible in a branch and a category
     *
     * @param Branch   $branch   Branch
     * @param Category $category Category
     *
     * @return array
     */
    public function findAllVisibleInBranchAndCategory(Branch $branch, Category $category)
    {
        return $this->createQueryBuilder('p')
            ->addSelect('pr')
            ->innerJoin('p.branches', 'b')
            ->innerJoin('p.producer', 'pr')
            ->where('p.availability != :availability')
            ->andWhere('b = :branch')
            ->andWhere('p.category = :category')
            ->andWhere('pr.deletedAt is null')
            ->addOrderBy('p.name')
            ->setParameter('availability', Product::AVAILABILITY_UNAVAILABLE)
            ->setParameter('branch', $branch)
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds a product visible in a branch by its id
     *
     * @param integer $id     id
     * @param Branch  $branch Branch
     *
     * @return Product|null
     */
    public function findOneByIdAndVisibleInBranch($id, Branch $branch)
    {
        try {
            return $this->createQueryBuilder('p')
                ->addSelect('pr')
                ->innerJoin('p.branches', 'b')
                ->innerJoin('p.producer', 'pr')
                ->where('p.id = :id')
                ->andwhere('p.availability != :availability')
                ->andWhere('b = :branch')
                ->addOrderBy('p.name')
                ->setParameter('id', $id)
                ->setParameter('availability', Product::AVAILABILITY_UNAVAILABLE)
                ->setParameter('branch', $branch)
                ->getQuery()
                ->getSingleResult();

        } catch (\Doctrine\ORM\NoResultException $e) {

            return null;
        }
    }

    /**
     * Finds products of the moment of a branch
     *
     * @param BranchOccurrence $branchOccurrence Branch occurrence
     * @param integer          $limit            Limit
     *
     * @return array
     */
    public function findOfTheMomentForBranchOccurrence(BranchOccurrence $branchOccurrence, $limit = 3)
    {
        // Retrieves all products of the moment ids and producer ids
        $qb = $this->filterAvailableForBranchOccurrence($branchOccurrence);
        $productsIds = $qb
            ->select('p.id as product_id, pr.id as producer_id')
            ->innerJoin('p.branches', 'b', Expr\Join::WITH, $qb->expr()->eq('b', ':branch'))
            ->andWhere('p.isOfTheMoment = true')
            ->andWhere('p.image IS NOT NULL')
            ->setParameter('branch', $branchOccurrence->getBranch())
            ->getQuery()
            ->getResult();

        if (empty($productsIds)) {
            return array();
        }

        // Groups products by producer
        $productsIdsByProducer = array();
        foreach ($productsIds as $productIds) {
            if (!array_key_exists($productIds['producer_id'], $productsIdsByProducer)) {
                $productsIdsByProducer[$productIds['producer_id']] = array();
            }
            $productsIdsByProducer[$productIds['producer_id']][] = $productIds['product_id'];
        }

        // Randomizes producers
        shuffle($productsIdsByProducer);

        // Truncates
        array_splice($productsIdsByProducer, $limit);

        // Creates products ids (1 random by remaining producer)
        $productsIds = array();
        foreach ($productsIdsByProducer as $producerProductsIds) {
            $productsIds[] = $producerProductsIds[array_rand($producerProductsIds)];
        }

        // Retrieves products
        $products = $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $productsIds)
            ->getQuery()
            ->getResult();

        // Randomizes products
        shuffle($products);

        return $products;
    }

    /**
     * Returns query builder for producer products
     *
     * @param Producer $producer
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getForProducerQueryBuilder(Producer $producer, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        return $qb->addSelect('b')
                ->leftJoin('p.branches', 'b')
                ->andWhere('p.producer = :producer')
                ->setParameter('producer', $producer)
                ->addOrderBy('p.name');
    }

    /**
     * Returns products of a producer
     *
     * @param Producer $producer
     *
     * @return array
     */
    public function findForProducer(Producer $producer)
    {
        return $this->getForProducerQueryBuilder($producer)
            ->orderBy('p.ref')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns query builder for available products
     *
     * @param BranchOccurrence $branchOccurrence Branch occurrence
     * @param QueryBuilder     $qb
     *
     * @return QueryBuilder
     */
    public function filterAvailableForBranchOccurrence(BranchOccurrence $branchOccurrence, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        return $qb->innerJoin('p.producer', 'pr')
            ->innerJoin('pr.producerAttendances', 'pra', Expr\Join::WITH, $qb->expr()->eq('pra.branchOccurrence', ':branchOccurrence'))
            ->andWhere('pra.isAttendee = true')
            ->andWhere(
                $qb->expr()->orx(
                    $qb->expr()->eq('p.availability', ':available'),
                    $qb->expr()->andx(
                        $qb->expr()->eq('p.availability', ':accordingToStock'),
                        $qb->expr()->gt('p.stock', 0)
                    ),
                    $qb->expr()->andx(
                        $qb->expr()->eq('p.availability', ':availableAt'),
                        $qb->expr()->lt('p.availableAt', ':begin')
                    )
                )
            )
            ->setParameter('branchOccurrence', $branchOccurrence)
            ->setParameter('available', Product::AVAILABILITY_AVAILABLE)
            ->setParameter('accordingToStock', Product::AVAILABILITY_ACCORDING_TO_STOCK)
            ->setParameter('availableAt', Product::AVAILABILITY_AVAILABLE_AT)
            ->setParameter('begin', $branchOccurrence->getBegin());
    }

    /**
     * Returns query builder for association products
     *
     * @param Association $association
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getForAssociationQueryBuilder(Association $association, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        return $qb->addSelect('b')
                ->addSelect('ahp')
                ->addSelect('pr')
                ->innerJoin('p.producer', 'pr')
                ->innerJoin('pr.associationHasProducer', 'ahp')
                ->innerJoin('ahp.association', 'a')
                ->leftJoin('ahp.branches', 'b')
                ->andWhere('a.id = :associationId')
                ->setParameter('associationId', $association->getId())
                ->addOrderBy('p.name')
                ->addGroupBy('p.id');
    }

    /**
     * Returns products of an association
     *
     * @param Association $association
     *
     * @return array
     */
    public function findForAssociation(Association $association)
    {
        return $this->getForAssociationQueryBuilder($association)->getQuery()->getResult();
    }

    /**
     * Returns queryBuilder to find out of stock products of a producer
     *
     * @param Producer $producer
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function getOutOfStockForProducerQueryBuilder(Producer $producer, QueryBuilder $qb = null)
    {
        $qb = null === $qb ? $this->createQueryBuilder('p') : $qb;

        return $qb->where('p.producer = :producer')
                ->setParameter('producer', $producer)
                ->andWhere('p.availability = :availability')
                ->setParameter('availability', Product::AVAILABILITY_ACCORDING_TO_STOCK)
                ->andWhere('p.stock <= 0');
    }

    /**
     * Returns count of out of stock products of a producer
     *
     * @param Producer $producer
     *
     * @return int
     */
    public function countOutOfStockProductsForProducer(Producer $producer)
    {
        $result = $this->getOutOfStockForProducerQueryBuilder($producer)
                ->select('COUNT(p.id) AS counter')
                ->getQuery()
                ->getSingleResult();

        return $result['counter'];
    }

    /**
     * Returns a complete list of the most sold products' id for each product
     *
     * @param null
     * 
     * @return array
     */
    public function FindOneByMostAssociatedProducts() {

        $conn = $this->getDoctrine()->getEntityManager()->getConnection();

        // Find all products
        /*$allProducts = 'SELECT product.id FROM product ORDER BY product.id';
        $stmt1 = $conn->prepare($allProducts);
        $stmt1->execute();
        $arrProds = $stmt1->fetchAll();*/

        $arrProds = ['68','69','70','71','72','73','74','75','76','77','78','79','80'];
        $prodList = array();
        foreach($arrProds as $prodId) {
            $productsInOrders = '
            SELECT sor1.product_id, COUNT(sor1.sales_order_id)
            FROM sales_order_row AS sor1
            JOIN sales_order_row AS sor2 ON (sor2.sales_order_id = sor1.sales_order_id AND sor2.product_id = :id)
            WHERE sor1.product_id != :id
            GROUP BY 1
            ORDER BY 2 DESC 
            LIMIT 3';
            $stmt2 = $conn->prepare($productsInOrders);
            //$stmt2->bindParam(':id', $prodId['id']);
            $stmt2->bindParam(':id',$prodId);
            $stmt2->execute();


            $allProductsItems = array();
            foreach ($stmt2->fetchAll() as $productList) {
                array_push($allProductsItems, $productList['product_id']);
            }
            $prodList[$prodId] = $allProductsItems;
        }
         return $prodList;
    }
    public function FindOneByMessage($message) {
        return $message;
    }
}
