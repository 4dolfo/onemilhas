<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Purchase
 *
 * @ORM\Table(name="purchase", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_purchase_Cards1_idx", columns={"cards_id"}), @ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class Purchase
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
     * @ORM\Column(name="description", type="string", length=2000, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="purchase_miles", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $purchaseMiles;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="purchase_date", type="datetime", nullable=false)
     */
    private $purchaseDate;

    /**
     * @var string
     *
     * @ORM\Column(name="cost_per_thousand", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $costPerThousand;

    /**
     * @var string
     *
     * @ORM\Column(name="total_cost", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $totalCost;

    /**
     * @var string
     *
     * @ORM\Column(name="aproved", type="string", length=1, nullable=true)
     */
    private $aproved;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="miles_due_date", type="datetime", nullable=false)
     */
    private $milesDueDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pay_date", type="datetime", nullable=true)
     */
    private $payDate;

    /**
     * @var string
     *
     * @ORM\Column(name="leftover", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $leftover = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=10, nullable=false)
     */
    private $status = 'W';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="contract_due_date", type="datetime", nullable=true)
     */
    private $contractDueDate;

    /**
     * @var string
     *
     * @ORM\Column(name="real_purchased", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $realPurchased = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="card_type", type="string", length=14, nullable=true)
     */
    private $cardType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="merge_date", type="datetime", nullable=true)
     */
    private $mergeDate;

    /**
     * @var string
     *
     * @ORM\Column(name="losses", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $losses = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="cost_per_thousand_purchase", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $costPerThousandPurchase = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="is_promo", type="string", length=5, nullable=false)
     */
    private $isPromo = 'false';

    /**
     * @var \Cards
     *
     * @ORM\ManyToOne(targetEntity="Cards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cards_id", referencedColumnName="id")
     * })
     */
    private $cards;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;


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
     * @return Purchase
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
     * Set purchaseMiles
     *
     * @param string $purchaseMiles
     * @return Purchase
     */
    public function setPurchaseMiles($purchaseMiles)
    {
        $this->purchaseMiles = $purchaseMiles;

        return $this;
    }

    /**
     * Get purchaseMiles
     *
     * @return string 
     */
    public function getPurchaseMiles()
    {
        return $this->purchaseMiles;
    }

    /**
     * Set purchaseDate
     *
     * @param \DateTime $purchaseDate
     * @return Purchase
     */
    public function setPurchaseDate($purchaseDate)
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    /**
     * Get purchaseDate
     *
     * @return \DateTime 
     */
    public function getPurchaseDate()
    {
        return $this->purchaseDate;
    }

    /**
     * Set costPerThousand
     *
     * @param string $costPerThousand
     * @return Purchase
     */
    public function setCostPerThousand($costPerThousand)
    {
        $this->costPerThousand = $costPerThousand;

        return $this;
    }

    /**
     * Get costPerThousand
     *
     * @return string 
     */
    public function getCostPerThousand()
    {
        return $this->costPerThousand;
    }

    /**
     * Set totalCost
     *
     * @param string $totalCost
     * @return Purchase
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
     * Set aproved
     *
     * @param string $aproved
     * @return Purchase
     */
    public function setAproved($aproved)
    {
        $this->aproved = $aproved;

        return $this;
    }

    /**
     * Get aproved
     *
     * @return string 
     */
    public function getAproved()
    {
        return $this->aproved;
    }

    /**
     * Set milesDueDate
     *
     * @param \DateTime $milesDueDate
     * @return Purchase
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
     * Set payDate
     *
     * @param \DateTime $payDate
     * @return Purchase
     */
    public function setPayDate($payDate)
    {
        $this->payDate = $payDate;

        return $this;
    }

    /**
     * Get payDate
     *
     * @return \DateTime 
     */
    public function getPayDate()
    {
        return $this->payDate;
    }

    /**
     * Set leftover
     *
     * @param string $leftover
     * @return Purchase
     */
    public function setLeftover($leftover)
    {
        $this->leftover = $leftover;

        return $this;
    }

    /**
     * Get leftover
     *
     * @return string 
     */
    public function getLeftover()
    {
        return $this->leftover;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Purchase
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
     * Set contractDueDate
     *
     * @param \DateTime $contractDueDate
     * @return Purchase
     */
    public function setContractDueDate($contractDueDate)
    {
        $this->contractDueDate = $contractDueDate;

        return $this;
    }

    /**
     * Get contractDueDate
     *
     * @return \DateTime 
     */
    public function getContractDueDate()
    {
        return $this->contractDueDate;
    }

    /**
     * Set realPurchased
     *
     * @param string $realPurchased
     * @return Purchase
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
     * Set cardType
     *
     * @param string $cardType
     * @return Purchase
     */
    public function setCardType($cardType)
    {
        $this->cardType = $cardType;

        return $this;
    }

    /**
     * Get cardType
     *
     * @return string 
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * Set mergeDate
     *
     * @param \DateTime $mergeDate
     * @return Purchase
     */
    public function setMergeDate($mergeDate)
    {
        $this->mergeDate = $mergeDate;

        return $this;
    }

    /**
     * Get mergeDate
     *
     * @return \DateTime 
     */
    public function getMergeDate()
    {
        return $this->mergeDate;
    }

    /**
     * Set losses
     *
     * @param string $losses
     * @return Purchase
     */
    public function setLosses($losses)
    {
        $this->losses = $losses;

        return $this;
    }

    /**
     * Get losses
     *
     * @return string 
     */
    public function getLosses()
    {
        return $this->losses;
    }

    /**
     * Set costPerThousandPurchase
     *
     * @param string $costPerThousandPurchase
     * @return Purchase
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
     * Set isPromo
     *
     * @param string $isPromo
     * @return Purchase
     */
    public function setIsPromo($isPromo)
    {
        $this->isPromo = $isPromo;

        return $this;
    }

    /**
     * Get isPromo
     *
     * @return string 
     */
    public function getIsPromo()
    {
        return $this->isPromo;
    }

    /**
     * Set cards
     *
     * @param \Cards $cards
     * @return Purchase
     */
    public function setCards(\Cards $cards = null)
    {
        $this->cards = $cards;

        return $this;
    }

    /**
     * Get cards
     *
     * @return \Cards 
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Set user
     *
     * @param \Businesspartner $user
     * @return Purchase
     */
    public function setUser(\Businesspartner $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Businesspartner 
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="payment_method", type="string", nullable=true)
     */
    private $paymentMethod = 'prepaid';

    /**
     * @var string
     *
     * @ORM\Column(name="payment_by", type="string", nullable=true)
     */
    private $paymentBy;

    /**
     * @var integer
     *
     * @ORM\Column(name="payment_days", type="integer", nullable=true)
     */
    private $paymentDays = '0';


    /**
     * Set paymentMethod
     *
     * @param string $paymentMethod
     * @return Purchase
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get paymentMethod
     *
     * @return string 
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set paymentBy
     *
     * @param string $paymentBy
     * @return Purchase
     */
    public function setPaymentBy($paymentBy)
    {
        $this->paymentBy = $paymentBy;

        return $this;
    }

    /**
     * Get paymentBy
     *
     * @return string 
     */
    public function getPaymentBy()
    {
        return $this->paymentBy;
    }

    /**
     * Set paymentDays
     *
     * @param integer $paymentDays
     * @return Purchase
     */
    public function setPaymentDays($paymentDays)
    {
        $this->paymentDays = $paymentDays;

        return $this;
    }

    /**
     * Get paymentDays
     *
     * @return integer 
     */
    public function getPaymentDays()
    {
        return $this->paymentDays;
    }
    /**
     * @var integer
     *
     * @ORM\Column(name="id_cotacao", type="integer", nullable=true)
     */
    private $idCotacao;


    /**
     * Set idCotacao
     *
     * @param integer $idCotacao
     * @return Purchase
     */
    public function setIdCotacao($idCotacao)
    {
        $this->idCotacao = $idCotacao;

        return $this;
    }

    /**
     * Get idCotacao
     *
     * @return integer 
     */
    public function getIdCotacao()
    {
        return $this->idCotacao;
    }
}
