<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Billsreceive
 *
 * @ORM\Table(name="billsreceive", indexes={@ORM\Index(name="fk_billsreceive_businesspartner", columns={"client_id"}), @ORM\Index(name="fk_billsreceive_billetreceive", columns={"billet_id"})})
 * @ORM\Entity
 */
class Billsreceive
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
     * @ORM\Column(name="receive_type", type="string", length=20, nullable=false)
     */
    private $receiveType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="due_date", type="datetime", nullable=false)
     */
    private $dueDate;

    /**
     * @var \Billetreceive
     *
     * @ORM\ManyToOne(targetEntity="Billetreceive")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="billet_id", referencedColumnName="id")
     * })
     */
    private $billet;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     * })
     */
    private $client;


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
     * @return Billsreceive
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
     * @return Billsreceive
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
     * @return Billsreceive
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
     * @return Billsreceive
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
     * @return Billsreceive
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
     * @return Billsreceive
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
     * @return Billsreceive
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
     * Set receiveType
     *
     * @param string $receiveType
     * @return Billsreceive
     */
    public function setReceiveType($receiveType)
    {
        $this->receiveType = $receiveType;

        return $this;
    }

    /**
     * Get receiveType
     *
     * @return string 
     */
    public function getReceiveType()
    {
        return $this->receiveType;
    }

    /**
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return Billsreceive
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
     * Set billet
     *
     * @param \Billetreceive $billet
     * @return Billsreceive
     */
    public function setBillet(\Billetreceive $billet = null)
    {
        $this->billet = $billet;

        return $this;
    }

    /**
     * Get billet
     *
     * @return \Billetreceive 
     */
    public function getBillet()
    {
        return $this->billet;
    }

    /**
     * Set client
     *
     * @param \Businesspartner $client
     * @return Billsreceive
     */
    public function setClient(\Businesspartner $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \Businesspartner 
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_process_date", type="datetime", nullable=false)
     */
    private $lastProcessDate;

    /**
     * Set lastProcessDate
     *
     * @param \DateTime $lastProcessDate
     * @return Billsreceive
     */
    public function setLastProcessDate($lastProcessDate)
    {
        $this->lastProcessDate = $lastProcessDate;

        return $this;
    }

    /**
     * Get lastProcessDate
     *
     * @return \DateTime 
     */
    public function getLastProcessDate()
    {
        return $this->lastProcessDate;
    }
    
}
