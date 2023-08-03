<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Billspay
 *
 * @ORM\Table(name="billspay", indexes={@ORM\Index(name="fk_billspay_businesspartner", columns={"provider_id"}), @ORM\Index(name="cards_id", columns={"cards_id"})})
 * @ORM\Entity
 */
class Billspay
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
     * @ORM\Column(name="status", type="string", length=1, nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=200, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="original_value", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $originalValue;

    /**
     * @var string
     *
     * @ORM\Column(name="actual_value", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $actualValue;

    /**
     * @var string
     *
     * @ORM\Column(name="tax", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $tax;

    /**
     * @var string
     *
     * @ORM\Column(name="discount", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $discount;

    /**
     * @var string
     *
     * @ORM\Column(name="account_type", type="string", length=20, nullable=false)
     */
    private $accountType;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_type", type="string", length=20, nullable=false)
     */
    private $paymentType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="due_date", type="datetime", nullable=false)
     */
    private $dueDate;

    /**
     * @var string
     *
     * @ORM\Column(name="already_paid", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $alreadyPaid = '0.00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_date", type="datetime", nullable=true)
     */
    private $paymentDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issue_date", type="datetime", nullable=true)
     */
    private $issueDate;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="provider_id", referencedColumnName="id")
     * })
     */
    private $provider;

    /**
     * @var \InternalCards
     *
     * @ORM\ManyToOne(targetEntity="InternalCards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cards_id", referencedColumnName="id")
     * })
     */
    private $cards;


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
     * Set status
     *
     * @param string $status
     * @return Billspay
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
     * Set description
     *
     * @param string $description
     * @return Billspay
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
     * Set originalValue
     *
     * @param string $originalValue
     * @return Billspay
     */
    public function setOriginalValue($originalValue)
    {
        $this->originalValue = $originalValue;

        return $this;
    }

    /**
     * Get originalValue
     *
     * @return string 
     */
    public function getOriginalValue()
    {
        return $this->originalValue;
    }

    /**
     * Set actualValue
     *
     * @param string $actualValue
     * @return Billspay
     */
    public function setActualValue($actualValue)
    {
        $this->actualValue = $actualValue;

        return $this;
    }

    /**
     * Get actualValue
     *
     * @return string 
     */
    public function getActualValue()
    {
        return $this->actualValue;
    }

    /**
     * Set tax
     *
     * @param string $tax
     * @return Billspay
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get tax
     *
     * @return string 
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set discount
     *
     * @param string $discount
     * @return Billspay
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return string 
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set accountType
     *
     * @param string $accountType
     * @return Billspay
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;

        return $this;
    }

    /**
     * Get accountType
     *
     * @return string 
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * Set paymentType
     *
     * @param string $paymentType
     * @return Billspay
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * Get paymentType
     *
     * @return string 
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return Billspay
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate
     *
     * @return \DateTime 
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set alreadyPaid
     *
     * @param string $alreadyPaid
     * @return Billspay
     */
    public function setAlreadyPaid($alreadyPaid)
    {
        $this->alreadyPaid = $alreadyPaid;

        return $this;
    }

    /**
     * Get alreadyPaid
     *
     * @return string 
     */
    public function getAlreadyPaid()
    {
        return $this->alreadyPaid;
    }

    /**
     * Set paymentDate
     *
     * @param \DateTime $paymentDate
     * @return Billspay
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    /**
     * Get paymentDate
     *
     * @return \DateTime 
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * Set issueDate
     *
     * @param \DateTime $issueDate
     * @return Billspay
     */
    public function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    /**
     * Get issueDate
     *
     * @return \DateTime 
     */
    public function getIssueDate()
    {
        return $this->issueDate;
    }

    /**
     * Set provider
     *
     * @param \Businesspartner $provider
     * @return Billspay
     */
    public function setProvider(\Businesspartner $provider = null)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return \Businesspartner 
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set cards
     *
     * @param \InternalCards $cards
     * @return Billspay
     */
    public function setCards(\InternalCards $cards = null)
    {
        $this->cards = $cards;

        return $this;
    }

    /**
     * Get cards
     *
     * @return \InternalCards 
     */
    public function getCards()
    {
        return $this->cards;
    }
}
