<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CostCenter
 *
 * @ORM\Table(name="cost_center")
 * @ORM\Entity
 */
class CostCenter
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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=1, nullable=false)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime", nullable=false)
     */
    private $timestamp;


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
     * Set name
     *
     * @param string $name
     * @return CostCenter
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
     * Set type
     *
     * @param string $type
     * @return CostCenter
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set timestamp
     *
     * @param \DateTime $timestamp
     * @return CostCenter
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return \DateTime 
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
    /**
     * @var \CostCenter
     *
     * @ORM\ManyToOne(targetEntity="CostCenter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_children", referencedColumnName="id")
     * })
     */
    private $idChildren;


    /**
     * Set idChildren
     *
     * @param \CostCenter $idChildren
     * @return CostCenter
     */
    public function setIdChildren(\CostCenter $idChildren = null)
    {
        $this->idChildren = $idChildren;

        return $this;
    }

    /**
     * Get idChildren
     *
     * @return \CostCenter 
     */
    public function getIdChildren()
    {
        return $this->idChildren;
    }
}
