<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Milesbench
 *
 * @ORM\Table(name="milesbench", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_milesbench_Cards1_idx", columns={"cards_id"})})
 * @ORM\Entity
 */
class Milesbench
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
     * @ORM\Column(name="leftover", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $leftover = '0.00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastchange", type="datetime", nullable=true)
     */
    private $lastchange;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="due_date", type="datetime", nullable=false)
     */
    private $dueDate;

    /**
     * @var string
     *
     * @ORM\Column(name="cost_per_thousand", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $costPerThousand = '0.00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="contract_due_date", type="datetime", nullable=true)
     */
    private $contractDueDate;

    /**
     * @var string
     *
     * @ORM\Column(name="total_card_leftover", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $totalCardLeftover;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_priority", type="datetime", nullable=true)
     */
    private $datePriority;

    /**
     * @var float
     *
     * @ORM\Column(name="miles_priority", type="float", precision=20, scale=2, nullable=true)
     */
    private $milesPriority = '0.00';

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set leftover
     *
     * @param string $leftover
     * @return Milesbench
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
     * Set lastchange
     *
     * @param \DateTime $lastchange
     * @return Milesbench
     */
    public function setLastchange($lastchange)
    {
        $this->lastchange = $lastchange;

        return $this;
    }

    /**
     * Get lastchange
     *
     * @return \DateTime 
     */
    public function getLastchange()
    {
        return $this->lastchange;
    }

    /**
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return Milesbench
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
     * Set costPerThousand
     *
     * @param string $costPerThousand
     * @return Milesbench
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
     * Set contractDueDate
     *
     * @param \DateTime $contractDueDate
     * @return Milesbench
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
     * Set totalCardLeftover
     *
     * @param string $totalCardLeftover
     * @return Milesbench
     */
    public function setTotalCardLeftover($totalCardLeftover)
    {
        $this->totalCardLeftover = $totalCardLeftover;

        return $this;
    }

    /**
     * Get totalCardLeftover
     *
     * @return string 
     */
    public function getTotalCardLeftover()
    {
        return $this->totalCardLeftover;
    }

    /**
     * Set datePriority
     *
     * @param \DateTime $datePriority
     * @return Milesbench
     */
    public function setDatePriority($datePriority)
    {
        $this->datePriority = $datePriority;

        return $this;
    }

    /**
     * Get datePriority
     *
     * @return \DateTime 
     */
    public function getDatePriority()
    {
        return $this->datePriority;
    }

    /**
     * Set milesPriority
     *
     * @param float $milesPriority
     * @return Milesbench
     */
    public function setMilesPriority($milesPriority)
    {
        $this->milesPriority = $milesPriority;

        return $this;
    }

    /**
     * Get milesPriority
     *
     * @return float 
     */
    public function getMilesPriority()
    {
        return $this->milesPriority;
    }

    /**
     * Set cards
     *
     * @param \Cards $cards
     * @return Milesbench
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
}
