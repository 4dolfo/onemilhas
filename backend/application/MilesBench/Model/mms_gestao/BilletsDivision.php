<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * BilletsDivision
 *
 * @ORM\Table(name="billets_division", indexes={@ORM\Index(name="billet_id", columns={"billet_id"})})
 * @ORM\Entity
 */
class BilletsDivision
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
     * @ORM\Column(name="due_date", type="datetime", nullable=false)
     */
    private $dueDate;

    /**
     * @var string
     *
     * @ORM\Column(name="actual_value", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $actualValue = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="paid", type="string", length=5, nullable=false)
     */
    private $paid = 'false';

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return BilletsDivision
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
     * Set actualValue
     *
     * @param string $actualValue
     * @return BilletsDivision
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
     * Set name
     *
     * @param string $name
     * @return BilletsDivision
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set paid
     *
     * @param string $paid
     * @return BilletsDivision
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid
     *
     * @return string 
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set billet
     *
     * @param \Billetreceive $billet
     * @return BilletsDivision
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
}
