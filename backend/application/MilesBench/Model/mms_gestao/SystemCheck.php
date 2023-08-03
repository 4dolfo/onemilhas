<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SystemCheck
 *
 * @ORM\Table(name="system_check", indexes={@ORM\Index(name="fk_business_id", columns={"businesspartner_id"}), @ORM\Index(name="fk_info_id", columns={"info_id"})})
 * @ORM\Entity
 */
class SystemCheck
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
     * @ORM\Column(name="issue_date", type="datetime", nullable=false)
     */
    private $issueDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="check_info", type="boolean", nullable=true)
     */
    private $checkInfo;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="businesspartner_id", referencedColumnName="id")
     * })
     */
    private $businesspartner;

    /**
     * @var \Info
     *
     * @ORM\ManyToOne(targetEntity="Info")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="info_id", referencedColumnName="id")
     * })
     */
    private $info;


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
     * Set issueDate
     *
     * @param \DateTime $issueDate
     * @return SystemCheck
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
     * Set checkInfo
     *
     * @param boolean $checkInfo
     * @return SystemCheck
     */
    public function setCheckInfo($checkInfo)
    {
        $this->checkInfo = $checkInfo;

        return $this;
    }

    /**
     * Get checkInfo
     *
     * @return boolean 
     */
    public function getCheckInfo()
    {
        return $this->checkInfo;
    }

    /**
     * Set businesspartner
     *
     * @param \Businesspartner $businesspartner
     * @return SystemCheck
     */
    public function setBusinesspartner(\Businesspartner $businesspartner = null)
    {
        $this->businesspartner = $businesspartner;

        return $this;
    }

    /**
     * Get businesspartner
     *
     * @return \Businesspartner 
     */
    public function getBusinesspartner()
    {
        return $this->businesspartner;
    }

    /**
     * Set info
     *
     * @param \Info $info
     * @return SystemCheck
     */
    public function setInfo(\Info $info = null)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Get info
     *
     * @return \Info 
     */
    public function getInfo()
    {
        return $this->info;
    }
}
