<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SalePurchases
 *
 * @ORM\Table(name="sale_purchases", indexes={@ORM\Index(name="sale_id", columns={"sale_id"}), @ORM\Index(name="purchase_id", columns={"purchase_id"})})
 * @ORM\Entity
 */
class SalePurchases
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Purchase
     *
     * @ORM\ManyToOne(targetEntity="Purchase")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_id", referencedColumnName="id")
     * })
     */
    private $purchase;

    /**
     * @var \Sale
     *
     * @ORM\ManyToOne(targetEntity="Sale")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sale_id", referencedColumnName="id")
     * })
     */
    private $sale;


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
     * Set purchase
     *
     * @param \Purchase $purchase
     * @return SalePurchases
     */
    public function setPurchase(\Purchase $purchase = null)
    {
        $this->purchase = $purchase;

        return $this;
    }

    /**
     * Get purchase
     *
     * @return \Purchase 
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * Set sale
     *
     * @param \Sale $sale
     * @return SalePurchases
     */
    public function setSale(\Sale $sale = null)
    {
        $this->sale = $sale;

        return $this;
    }

    /**
     * Get sale
     *
     * @return \Sale 
     */
    public function getSale()
    {
        return $this->sale;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="miles_used", type="float", precision=20, scale=2, nullable=true)
     */
    private $milesUsed = '0.00';


    /**
     * Set milesUsed
     *
     * @param float $milesUsed
     * @return SalePurchases
     */
    public function setMilesUsed($milesUsed)
    {
        $this->milesUsed = $milesUsed;

        return $this;
    }

    /**
     * Get milesUsed
     *
     * @return float 
     */
    public function getMilesUsed()
    {
        return $this->milesUsed;
    }
}
