<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Isics\OpenMiamMiamBundle\Entity\ProductAssociation
 *
 * @ORM\Table(name="product_matches")
 * @ORM\Entity(repositoryClass="Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\ProductMatchingRepository")
 */
class ProductMatching
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $product
     * 
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\Column(name="product_id", type="integer")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var integer $complementary_product
     * 
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\Column(name="complementary_product_id", type="integer")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $complementary_product;

    /**
     * @var integer $nb_common_orders
     * 
     * @ORM\Column(name="nb_common_orders", type="integer")
     */
    private $nb_common_orders;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set product
     *
     * @param Product $product
     *
     * @return ProductMatching
     */
    public function setProduct($product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set complementary_produt
     *
     * @param Product $complementary_product
     *
     * @return ProductMatching
     */
    public function setComplementaryProduct($complementary_product = null)
    {
        $this->complementary_product = $complementary_product;

        return $this;
    }

    /**
     * Get complementary_product
     *
     * @return Product
     */
    public function getComplementaryProduct()
    {
        return $this->complementary_product;
    }

    /**
     * Set nb_common_order
     *
     * @param integer $nb_common_orders
     *
     * @return integer
     */
    public function setNbCommonOrders($nb_common_orders = null)
    {
        $this->nb_common_orders = $nb_common_orders;

        return $this;
    }

    /**
     * Get nb_common_orders
     *
     * @return integer
     */
    public function getNbCommonOrders()
    {
        return $this->nb_common_orders;
    }
}
