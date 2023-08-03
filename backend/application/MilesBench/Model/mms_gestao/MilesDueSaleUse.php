<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * MilesDueSaleUse
 *
 * @ORM\Table(name="miles_due_sale_use", indexes={@ORM\Index(name="miles_due_date_id", columns={"miles_due_date_id"}), @ORM\Index(name="sale_id", columns={"sale_id"})})
 * @ORM\Entity
 */
class MilesDueSaleUse
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
     * @var string
     *
     * @ORM\Column(name="miles_used", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $milesUsed = '0.00';

    /**
     * @var \PurchaseMilesDueDate
     *
     * @ORM\ManyToOne(targetEntity="PurchaseMilesDueDate")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="miles_due_date_id", referencedColumnName="id")
     * })
     */
    private $milesDueDate;

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
     * Set milesUsed
     *
     * @param string $milesUsed
     * @return MilesDueSaleUse
     */
    public function setMilesUsed($milesUsed)
    {
        $this->milesUsed = $milesUsed;

        return $this;
    }

    /**
     * Get milesUsed
     *
     * @return string 
     */
    public function getMilesUsed()
    {
        return $this->milesUsed;
    }

    /**
     * Set milesDueDate
     *
     * @param \PurchaseMilesDueDate $milesDueDate
     * @return MilesDueSaleUse
     */
    public function setMilesDueDate(\PurchaseMilesDueDate $milesDueDate = null)
    {
        $this->milesDueDate = $milesDueDate;

        return $this;
    }

    /**
     * Get milesDueDate
     *
     * @return \PurchaseMilesDueDate 
     */
    public function getMilesDueDate()
    {
        return $this->milesDueDate;
    }

    /**
     * Set sale
     *
     * @param \Sale $sale
     * @return MilesDueSaleUse
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
}
