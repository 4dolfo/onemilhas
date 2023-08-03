<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PlansPromos
 *
 * @ORM\Table(name="plans_promos", indexes={@ORM\Index(name="plan", columns={"plan"})})
 * @ORM\Entity
 */
class PlansPromos
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
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=false)
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=5, nullable=false)
     */
    private $status = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="clients", type="string", length=2048, nullable=false)
     */
    private $clients;

    /**
     * @var \SalePlans
     *
     * @ORM\ManyToOne(targetEntity="SalePlans")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plan", referencedColumnName="id")
     * })
     */
    private $plan;


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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return PlansPromos
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return PlansPromos
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return PlansPromos
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
     * Set clients
     *
     * @param string $clients
     * @return PlansPromos
     */
    public function setClients($clients)
    {
        $this->clients = $clients;

        return $this;
    }

    /**
     * Get clients
     *
     * @return string 
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Set plan
     *
     * @param \SalePlans $plan
     * @return PlansPromos
     */
    public function setPlan(\SalePlans $plan = null)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get plan
     *
     * @return \SalePlans 
     */
    public function getPlan()
    {
        return $this->plan;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="for_all_clients", type="string", length=5, nullable=false)
     */
    private $forAllClients = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="discount_type", type="string", nullable=false)
     */
    private $discountType = 'D';

    /**
     * @var float
     *
     * @ORM\Column(name="discount_markup", type="float", precision=20, scale=2, nullable=false)
     */
    private $discountMarkup = '0.00';


    /**
     * Set forAllClients
     *
     * @param string $forAllClients
     * @return PlansPromos
     */
    public function setForAllClients($forAllClients)
    {
        $this->forAllClients = $forAllClients;

        return $this;
    }

    /**
     * Get forAllClients
     *
     * @return string 
     */
    public function getForAllClients()
    {
        return $this->forAllClients;
    }

    /**
     * Set discountType
     *
     * @param string $discountType
     * @return PlansPromos
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;

        return $this;
    }

    /**
     * Get discountType
     *
     * @return string 
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * Set discountMarkup
     *
     * @param float $discountMarkup
     * @return PlansPromos
     */
    public function setDiscountMarkup($discountMarkup)
    {
        $this->discountMarkup = $discountMarkup;

        return $this;
    }

    /**
     * Get discountMarkup
     *
     * @return float 
     */
    public function getDiscountMarkup()
    {
        return $this->discountMarkup;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="airlines", type="string", length=2048, nullable=true)
     */
    private $airlines;

    /**
     * @var string
     *
     * @ORM\Column(name="airlines_types", type="string", length=2048, nullable=true)
     */
    private $airlinesTypes;


    /**
     * Set airlines
     *
     * @param string $airlines
     * @return PlansPromos
     */
    public function setAirlines($airlines)
    {
        $this->airlines = $airlines;

        return $this;
    }

    /**
     * Get airlines
     *
     * @return string 
     */
    public function getAirlines()
    {
        return $this->airlines;
    }

    /**
     * Set airlinesTypes
     *
     * @param string $airlinesTypes
     * @return PlansPromos
     */
    public function setAirlinesTypes($airlinesTypes)
    {
        $this->airlinesTypes = $airlinesTypes;

        return $this;
    }

    /**
     * Get airlinesTypes
     *
     * @return string 
     */
    public function getAirlinesTypes()
    {
        return $this->airlinesTypes;
    }
}
