<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * OnlinePax
 *
 * @ORM\Table(name="online_pax", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_online_pax_order", columns={"order_id"})})
 * @ORM\Entity
 */
class OnlinePax
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
     * @ORM\Column(name="pax_name", type="string", length=90, nullable=false)
     */
    private $paxName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthdate", type="datetime", nullable=true)
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=200, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=200, nullable=true)
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=1, nullable=false)
     */
    private $gender = 'M';

    /**
     * @var string
     *
     * @ORM\Column(name="is_newborn", type="string", length=1, nullable=false)
     */
    private $isNewborn = 'N';

    /**
     * @var string
     *
     * @ORM\Column(name="is_child", type="string", length=1, nullable=false)
     */
    private $isChild = 'N';

    /**
     * @var string
     *
     * @ORM\Column(name="identification", type="string", length=14, nullable=true)
     */
    private $identification;

    /**
     * @var string
     *
     * @ORM\Column(name="pax_last_name", type="string", length=50, nullable=true)
     */
    private $paxLastName;

    /**
     * @var string
     *
     * @ORM\Column(name="pax_agnome", type="string", length=50, nullable=true)
     */
    private $paxAgnome;

    /**
     * @var \OnlineOrder
     *
     * @ORM\ManyToOne(targetEntity="OnlineOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     * })
     */
    private $order;


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
     * Set paxName
     *
     * @param string $paxName
     * @return OnlinePax
     */
    public function setPaxName($paxName)
    {
        $this->paxName = $paxName;

        return $this;
    }

    /**
     * Get paxName
     *
     * @return string 
     */
    public function getPaxName()
    {
        return $this->paxName;
    }

    /**
     * Set birthdate
     *
     * @param \DateTime $birthdate
     * @return OnlinePax
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime 
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return OnlinePax
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return OnlinePax
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string 
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return OnlinePax
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string 
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set isNewborn
     *
     * @param string $isNewborn
     * @return OnlinePax
     */
    public function setIsNewborn($isNewborn)
    {
        $this->isNewborn = $isNewborn;

        return $this;
    }

    /**
     * Get isNewborn
     *
     * @return string 
     */
    public function getIsNewborn()
    {
        return $this->isNewborn;
    }

    /**
     * Set isChild
     *
     * @param string $isChild
     * @return OnlinePax
     */
    public function setIsChild($isChild)
    {
        $this->isChild = $isChild;

        return $this;
    }

    /**
     * Get isChild
     *
     * @return string 
     */
    public function getIsChild()
    {
        return $this->isChild;
    }

    /**
     * Set identification
     *
     * @param string $identification
     * @return OnlinePax
     */
    public function setIdentification($identification)
    {
        $this->identification = $identification;

        return $this;
    }

    /**
     * Get identification
     *
     * @return string 
     */
    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * Set paxLastName
     *
     * @param string $paxLastName
     * @return OnlinePax
     */
    public function setPaxLastName($paxLastName)
    {
        $this->paxLastName = $paxLastName;

        return $this;
    }

    /**
     * Get paxLastName
     *
     * @return string 
     */
    public function getPaxLastName()
    {
        return $this->paxLastName;
    }

    /**
     * Set paxAgnome
     *
     * @param string $paxAgnome
     * @return OnlinePax
     */
    public function setPaxAgnome($paxAgnome)
    {
        $this->paxAgnome = $paxAgnome;

        return $this;
    }

    /**
     * Get paxAgnome
     *
     * @return string 
     */
    public function getPaxAgnome()
    {
        return $this->paxAgnome;
    }

    /**
     * Set order
     *
     * @param \OnlineOrder $order
     * @return OnlinePax
     */
    public function setOrder(\OnlineOrder $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \OnlineOrder 
     */
    public function getOrder()
    {
        return $this->order;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="passaporte", type="string", length=150, nullable=true)
     */
    private $passaporte;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_passaporte", type="datetime", nullable=true)
     */
    private $dataPassaporte;


    /**
     * Set passaporte
     *
     * @param string $passaporte
     * @return OnlinePax
     */
    public function setPassaporte($passaporte)
    {
        $this->passaporte = $passaporte;

        return $this;
    }

    /**
     * Get passaporte
     *
     * @return string 
     */
    public function getPassaporte()
    {
        return $this->passaporte;
    }

    /**
     * Set dataPassaporte
     *
     * @param \DateTime $dataPassaporte
     * @return OnlinePax
     */
    public function setDataPassaporte($dataPassaporte)
    {
        $this->dataPassaporte = $dataPassaporte;

        return $this;
    }

    /**
     * Get dataPassaporte
     *
     * @return \DateTime 
     */
    public function getDataPassaporte()
    {
        return $this->dataPassaporte;
    }
}
