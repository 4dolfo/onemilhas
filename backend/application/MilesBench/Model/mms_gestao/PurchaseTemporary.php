<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseTemporary
 *
 * @ORM\Table(name="purchase_temporary", indexes={@ORM\Index(name="id_purchase", columns={"id_purchase"}), @ORM\Index(name="purchase_temporary_ibfk_2", columns={"old_purchase"})})
 * @ORM\Entity
 */
class PurchaseTemporary
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
     * @ORM\Column(name="description", type="string", length=150, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="cost_per_thousand_purchase", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $costPerThousandPurchase;

    /**
     * @var string
     *
     * @ORM\Column(name="real_purchased", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $realPurchased;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=false)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime", nullable=false)
     */
    private $createAt;

    /**
     * @var string
     *
     * @ORM\Column(name="user_name", type="string", length=150, nullable=false)
     */
    private $userName;

    /**
     * @var string
     *
     * @ORM\Column(name="total_cost", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $totalCost;

    /**
     * @var \Purchase
     *
     * @ORM\ManyToOne(targetEntity="Purchase")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_purchase", referencedColumnName="id")
     * })
     */
    private $idPurchase;

    /**
     * @var \Purchase
     *
     * @ORM\ManyToOne(targetEntity="Purchase")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="old_purchase", referencedColumnName="id")
     * })
     */
    private $oldPurchase;


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
     * Set description
     *
     * @param string $description
     * @return PurchaseTemporary
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set costPerThousandPurchase
     *
     * @param string $costPerThousandPurchase
     * @return PurchaseTemporary
     */
    public function setCostPerThousandPurchase($costPerThousandPurchase)
    {
        $this->costPerThousandPurchase = $costPerThousandPurchase;

        return $this;
    }

    /**
     * Get costPerThousandPurchase
     *
     * @return string 
     */
    public function getCostPerThousandPurchase()
    {
        return $this->costPerThousandPurchase;
    }

    /**
     * Set realPurchased
     *
     * @param string $realPurchased
     * @return PurchaseTemporary
     */
    public function setRealPurchased($realPurchased)
    {
        $this->realPurchased = $realPurchased;

        return $this;
    }

    /**
     * Get realPurchased
     *
     * @return string 
     */
    public function getRealPurchased()
    {
        return $this->realPurchased;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return PurchaseTemporary
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     * @return PurchaseTemporary
     */
    public function setCreateAt($createAt)
    {
        $this->createAt = $createAt;

        return $this;
    }

    /**
     * Get createAt
     *
     * @return \DateTime 
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * Set userName
     *
     * @param string $userName
     * @return PurchaseTemporary
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string 
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set totalCost
     *
     * @param string $totalCost
     * @return PurchaseTemporary
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;

        return $this;
    }

    /**
     * Get totalCost
     *
     * @return string 
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * Set idPurchase
     *
     * @param \Purchase $idPurchase
     * @return PurchaseTemporary
     */
    public function setIdPurchase(\Purchase $idPurchase = null)
    {
        $this->idPurchase = $idPurchase;

        return $this;
    }

    /**
     * Get idPurchase
     *
     * @return \Purchase 
     */
    public function getIdPurchase()
    {
        return $this->idPurchase;
    }

    /**
     * Set oldPurchase
     *
     * @param \Purchase $oldPurchase
     * @return PurchaseTemporary
     */
    public function setOldPurchase(\Purchase $oldPurchase = null)
    {
        $this->oldPurchase = $oldPurchase;

        return $this;
    }

    /**
     * Get oldPurchase
     *
     * @return \Purchase 
     */
    public function getOldPurchase()
    {
        return $this->oldPurchase;
    }
}
