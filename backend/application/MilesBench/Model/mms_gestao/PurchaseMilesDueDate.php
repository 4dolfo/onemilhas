<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseMilesDueDate
 *
 * @ORM\Table(name="purchase_miles_due_date", indexes={@ORM\Index(name="purchase_id", columns={"purchase_id"})})
 * @ORM\Entity
 */
class PurchaseMilesDueDate
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
     * @var \DateTime
     *
     * @ORM\Column(name="miles_due_date", type="datetime", nullable=false)
     */
    private $milesDueDate;

    /**
     * @var string
     *
     * @ORM\Column(name="miles", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $miles = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="miles_original", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $milesOriginal = '0.00';

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set milesDueDate
     *
     * @param \DateTime $milesDueDate
     * @return PurchaseMilesDueDate
     */
    public function setMilesDueDate($milesDueDate)
    {
        $this->milesDueDate = $milesDueDate;

        return $this;
    }

    /**
     * Get milesDueDate
     *
     * @return \DateTime 
     */
    public function getMilesDueDate()
    {
        return $this->milesDueDate;
    }

    /**
     * Set miles
     *
     * @param string $miles
     * @return PurchaseMilesDueDate
     */
    public function setMiles($miles)
    {
        $this->miles = $miles;

        return $this;
    }

    /**
     * Get miles
     *
     * @return string 
     */
    public function getMiles()
    {
        return $this->miles;
    }

    /**
     * Set milesOriginal
     *
     * @param string $milesOriginal
     * @return PurchaseMilesDueDate
     */
    public function setMilesOriginal($milesOriginal)
    {
        $this->milesOriginal = $milesOriginal;

        return $this;
    }

    /**
     * Get milesOriginal
     *
     * @return string 
     */
    public function getMilesOriginal()
    {
        return $this->milesOriginal;
    }

    /**
     * Set purchase
     *
     * @param \Purchase $purchase
     * @return PurchaseMilesDueDate
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
}
