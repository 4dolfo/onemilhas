<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Billetreceive
 *
 * @ORM\Table(name="billetreceive", indexes={@ORM\Index(name="fk_billetreceive_businesspartner", columns={"client_id"}), @ORM\Index(name="billing_partner_id", columns={"billing_partner_id"})})
 * @ORM\Entity
 */
class Billetreceive
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
     * @ORM\Column(name="description", type="text", nullable=true)
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
     * @var \DateTime
     *
     * @ORM\Column(name="due_date", type="datetime", nullable=false)
     */
    private $dueDate;

    /**
     * @var string
     *
     * @ORM\Column(name="doc_number", type="string", length=11, nullable=true)
     */
    private $docNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="our_number", type="string", length=11, nullable=true)
     */
    private $ourNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issue_date", type="datetime", nullable=false)
     */
    private $issueDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_date", type="datetime", nullable=true)
     */
    private $paymentDate;

    /**
     * @var string
     *
     * @ORM\Column(name="already_paid", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $alreadyPaid = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="bank", type="string", length=30, nullable=true)
     */
    private $bank;

    /**
     * @var string
     *
     * @ORM\Column(name="checkin_state", type="string", length=20, nullable=true)
     */
    private $checkinState;

    /**
     * @var string
     *
     * @ORM\Column(name="has_billet", type="string", length=5, nullable=false)
     */
    private $hasBillet = 'true';

    /**
     * @var string
     *
     * @ORM\Column(name="used_commission", type="string", length=5, nullable=true)
     */
    private $usedCommission = 'false';

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
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="billing_partner_id", referencedColumnName="id")
     * })
     */
    private $billingPartner;


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
     * @return Billetreceive
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
     * @return Billetreceive
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
     * @return Billetreceive
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
     * @return Billetreceive
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
     * @return Billetreceive
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
     * @return Billetreceive
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
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return Billetreceive
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
     * Set docNumber
     *
     * @param string $docNumber
     * @return Billetreceive
     */
    public function setDocNumber($docNumber)
    {
        $this->docNumber = $docNumber;

        return $this;
    }

    /**
     * Get docNumber
     *
     * @return string 
     */
    public function getDocNumber()
    {
        return $this->docNumber;
    }

    /**
     * Set ourNumber
     *
     * @param string $ourNumber
     * @return Billetreceive
     */
    public function setOurNumber($ourNumber)
    {
        $this->ourNumber = $ourNumber;

        return $this;
    }

    /**
     * Get ourNumber
     *
     * @return string 
     */
    public function getOurNumber()
    {
        return $this->ourNumber;
    }

    /**
     * Set issueDate
     *
     * @param \DateTime $issueDate
     * @return Billetreceive
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
     * Set paymentDate
     *
     * @param \DateTime $paymentDate
     * @return Billetreceive
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
     * Set alreadyPaid
     *
     * @param string $alreadyPaid
     * @return Billetreceive
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
     * Set bank
     *
     * @param string $bank
     * @return Billetreceive
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * Get bank
     *
     * @return string 
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * Set checkinState
     *
     * @param string $checkinState
     * @return Billetreceive
     */
    public function setCheckinState($checkinState)
    {
        $this->checkinState = $checkinState;

        return $this;
    }

    /**
     * Get checkinState
     *
     * @return string 
     */
    public function getCheckinState()
    {
        return $this->checkinState;
    }

    /**
     * Set hasBillet
     *
     * @param string $hasBillet
     * @return Billetreceive
     */
    public function setHasBillet($hasBillet)
    {
        $this->hasBillet = $hasBillet;

        return $this;
    }

    /**
     * Get hasBillet
     *
     * @return string 
     */
    public function getHasBillet()
    {
        return $this->hasBillet;
    }

    /**
     * Set usedCommission
     *
     * @param string $usedCommission
     * @return Billetreceive
     */
    public function setUsedCommission($usedCommission)
    {
        $this->usedCommission = $usedCommission;

        return $this;
    }

    /**
     * Get usedCommission
     *
     * @return string 
     */
    public function getUsedCommission()
    {
        return $this->usedCommission;
    }

    /**
     * Set client
     *
     * @param \Businesspartner $client
     * @return Billetreceive
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
     * Set billingPartner
     *
     * @param \Businesspartner $billingPartner
     * @return Billetreceive
     */
    public function setBillingPartner(\Businesspartner $billingPartner = null)
    {
        $this->billingPartner = $billingPartner;

        return $this;
    }

    /**
     * Get billingPartner
     *
     * @return \Businesspartner 
     */
    public function getBillingPartner()
    {
        return $this->billingPartner;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="sent_conta_azul", type="string", length=5, nullable=true)
     */
    private $sentContaAzul = 'false';


    /**
     * Set sentContaAzul
     *
     * @param string $sentContaAzul
     * @return Billetreceive
     */
    public function setSentContaAzul($sentContaAzul)
    {
        $this->sentContaAzul = $sentContaAzul;

        return $this;
    }

    /**
     * Get sentContaAzul
     *
     * @return string 
     */
    public function getSentContaAzul()
    {
        return $this->sentContaAzul;
    }
}
