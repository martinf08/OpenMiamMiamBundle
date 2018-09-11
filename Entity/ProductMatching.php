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
     * @ORM\OneToOne(targetEntity="Product")
     * @ORM\Column(name="product_id", type="integer")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var integer $firstMatchProduct
     *
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\Column(name="first_match_product", type="integer", nullable=true)
     * @ORM\JoinColumn(name="first_match_product", referencedColumnName="id")
     */
    private $firstMatchProduct;

    /**
     * @var integer $secondMatchProduct
     *
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\Column(name="second_match_product", type="integer", nullable=true)
     * @ORM\JoinColumn(name="second_match_product", referencedColumnName="id")
     */
    private $secondMatchProduct;

    /**
     * @var integer $thirdMatchProduct
     *
     * @ORM\ManyToOne(targetEntity="Product")
     * @ORM\Column(name="third_match_product", type="integer", nullable=true)
     * @ORM\JoinColumn(name="third_match_product", referencedColumnName="id")
     */
    private $thirdMatchProduct;

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
     * @param integer $product
     *
     * @return ProductAssociation
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
     * Set firstMatchProduct
     *
     * @param integer $firstMatchProduct
     *
     * @return ProductAssociation
     */
    public function setFirstMatchProduct($firstMatchProduct = null)
    {
        $this->firstMatchProduct = $firstMatchProduct;

        return $this;
    }

    /**
     * Get firstMatchProduct
     *
     * @return integer
     */
    public function getFirstMatchProduct()
    {
        return $this->firstMatchProduct;
    }

    /**
     * Set secondMatchProduct
     *
     * @param integer $secondMatchProduct
     *
     * @return ProductAssociation
     */
    public function setSecondMatchProduct($secondMatchProduct = null)
    {
        $this->secondMatchProduct = $secondMatchProduct;

        return $this;
    }

    /**
     * Get secondMatchProduct
     *
     * @return integer
     */
    public function getSecondMatchProduct()
    {
        return $this->secondMatchProduct;
    }

    /**
     * Set thirdMatchProduct
     *
     * @param integer $thirdMatchProduct
     *
     * @return ProductAssociation
     */
    public function setThirdMatchProduct($thirdMatchProduct = null)
    {
        $this->thirdMatchProduct = $thirdMatchProduct;

        return $this;
    }

    /**
     * Get thirdMatchProduct
     *
     * @return integer
     */
    public function getThirdMatchProduct()
    {
        return $this->thirdMatchProduct;
    }
}
