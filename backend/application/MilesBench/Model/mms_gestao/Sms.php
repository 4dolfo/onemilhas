<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Sms
 *
 * @ORM\Table(name="sms", indexes={@ORM\Index(name="user_id", columns={"system_user_id"})})
 * @ORM\Entity
 */
class Sms
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
     * @ORM\Column(name="to_number", type="string", length=32, nullable=false)
     */
    private $toNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=false)
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="status_code", type="string", length=255, nullable=true)
     */
    private $statusCode;

    /**
     * @var string
     *
     * @ORM\Column(name="status_description", type="string", length=255, nullable=true)
     */
    private $statusDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="detail_code", type="string", length=255, nullable=true)
     */
    private $detailCode;

    /**
     * @var string
     *
     * @ORM\Column(name="detail_description", type="string", length=255, nullable=true)
     */
    private $detailDescription;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issue_date", type="datetime", nullable=false)
     */
    private $issueDate;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="system_user_id", referencedColumnName="id")
     * })
     */
    private $systemUser;


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
     * Set toNumber
     *
     * @param string $toNumber
     * @return Sms
     */
    public function setToNumber($toNumber)
    {
        $this->toNumber = $toNumber;

        return $this;
    }

    /**
     * Get toNumber
     *
     * @return string 
     */
    public function getToNumber()
    {
        return $this->toNumber;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Sms
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set statusCode
     *
     * @param string $statusCode
     * @return Sms
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Get statusCode
     *
     * @return string 
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set statusDescription
     *
     * @param string $statusDescription
     * @return Sms
     */
    public function setStatusDescription($statusDescription)
    {
        $this->statusDescription = $statusDescription;

        return $this;
    }

    /**
     * Get statusDescription
     *
     * @return string 
     */
    public function getStatusDescription()
    {
        return $this->statusDescription;
    }

    /**
     * Set detailCode
     *
     * @param string $detailCode
     * @return Sms
     */
    public function setDetailCode($detailCode)
    {
        $this->detailCode = $detailCode;

        return $this;
    }

    /**
     * Get detailCode
     *
     * @return string 
     */
    public function getDetailCode()
    {
        return $this->detailCode;
    }

    /**
     * Set detailDescription
     *
     * @param string $detailDescription
     * @return Sms
     */
    public function setDetailDescription($detailDescription)
    {
        $this->detailDescription = $detailDescription;

        return $this;
    }

    /**
     * Get detailDescription
     *
     * @return string 
     */
    public function getDetailDescription()
    {
        return $this->detailDescription;
    }

    /**
     * Set issueDate
     *
     * @param \DateTime $issueDate
     * @return Sms
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
     * Set systemUser
     *
     * @param \Businesspartner $systemUser
     * @return Sms
     */
    public function setSystemUser(\Businesspartner $systemUser = null)
    {
        $this->systemUser = $systemUser;

        return $this;
    }

    /**
     * Get systemUser
     *
     * @return \Businesspartner 
     */
    public function getSystemUser()
    {
        return $this->systemUser;
    }
}
