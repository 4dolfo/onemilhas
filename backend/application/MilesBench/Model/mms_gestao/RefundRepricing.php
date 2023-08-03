<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * RefundRepricing
 *
 * @ORM\Table(name="refund_repricing", indexes={@ORM\Index(name="airline_id", columns={"airline_id"}), @ORM\Index(name="operation_plan", columns={"operation_plan"})})
 * @ORM\Entity
 */
class RefundRepricing
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
     * @ORM\Column(name="type", type="string", length=15, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="cost_national", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $costNational = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="national_before_boarding", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $nationalBeforeBoarding = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="national_after_boarding", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $nationalAfterBoarding = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="north_america_before_boarding", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $northAmericaBeforeBoarding = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="north_america_after_boarding", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $northAmericaAfterBoarding = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="international_before_boarding", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $internationalBeforeBoarding = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="international_after_boarding", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $internationalAfterBoarding = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="south_america_before_boarding", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $southAmericaBeforeBoarding = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="south_america_after_boarding", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $southAmericaAfterBoarding = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="national_before_boarding_cost", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $nationalBeforeBoardingCost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="national_after_boarding_cost", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $nationalAfterBoardingCost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="north_america_before_boarding_cost", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $northAmericaBeforeBoardingCost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="north_america_after_boarding_cost", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $northAmericaAfterBoardingCost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="international_before_boarding_cost", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $internationalBeforeBoardingCost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="international_after_boarding_cost", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $internationalAfterBoardingCost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="south_america_before_boarding_cost", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $southAmericaBeforeBoardingCost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="south_america_after_boarding_cost", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $southAmericaAfterBoardingCost = '0.00';

    /**
     * @var \AirlineOperationsPlan
     *
     * @ORM\ManyToOne(targetEntity="AirlineOperationsPlan")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="operation_plan", referencedColumnName="id")
     * })
     */
    private $operationPlan;

    /**
     * @var \Airline
     *
     * @ORM\ManyToOne(targetEntity="Airline")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="airline_id", referencedColumnName="id")
     * })
     */
    private $airline;


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
     * Set type
     *
     * @param string $type
     * @return RefundRepricing
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
     * Set costNational
     *
     * @param string $costNational
     * @return RefundRepricing
     */
    public function setCostNational($costNational)
    {
        $this->costNational = $costNational;

        return $this;
    }

    /**
     * Get costNational
     *
     * @return string 
     */
    public function getCostNational()
    {
        return $this->costNational;
    }

    /**
     * Set nationalBeforeBoarding
     *
     * @param string $nationalBeforeBoarding
     * @return RefundRepricing
     */
    public function setNationalBeforeBoarding($nationalBeforeBoarding)
    {
        $this->nationalBeforeBoarding = $nationalBeforeBoarding;

        return $this;
    }

    /**
     * Get nationalBeforeBoarding
     *
     * @return string 
     */
    public function getNationalBeforeBoarding()
    {
        return $this->nationalBeforeBoarding;
    }

    /**
     * Set nationalAfterBoarding
     *
     * @param string $nationalAfterBoarding
     * @return RefundRepricing
     */
    public function setNationalAfterBoarding($nationalAfterBoarding)
    {
        $this->nationalAfterBoarding = $nationalAfterBoarding;

        return $this;
    }

    /**
     * Get nationalAfterBoarding
     *
     * @return string 
     */
    public function getNationalAfterBoarding()
    {
        return $this->nationalAfterBoarding;
    }

    /**
     * Set northAmericaBeforeBoarding
     *
     * @param string $northAmericaBeforeBoarding
     * @return RefundRepricing
     */
    public function setNorthAmericaBeforeBoarding($northAmericaBeforeBoarding)
    {
        $this->northAmericaBeforeBoarding = $northAmericaBeforeBoarding;

        return $this;
    }

    /**
     * Get northAmericaBeforeBoarding
     *
     * @return string 
     */
    public function getNorthAmericaBeforeBoarding()
    {
        return $this->northAmericaBeforeBoarding;
    }

    /**
     * Set northAmericaAfterBoarding
     *
     * @param string $northAmericaAfterBoarding
     * @return RefundRepricing
     */
    public function setNorthAmericaAfterBoarding($northAmericaAfterBoarding)
    {
        $this->northAmericaAfterBoarding = $northAmericaAfterBoarding;

        return $this;
    }

    /**
     * Get northAmericaAfterBoarding
     *
     * @return string 
     */
    public function getNorthAmericaAfterBoarding()
    {
        return $this->northAmericaAfterBoarding;
    }

    /**
     * Set internationalBeforeBoarding
     *
     * @param string $internationalBeforeBoarding
     * @return RefundRepricing
     */
    public function setInternationalBeforeBoarding($internationalBeforeBoarding)
    {
        $this->internationalBeforeBoarding = $internationalBeforeBoarding;

        return $this;
    }

    /**
     * Get internationalBeforeBoarding
     *
     * @return string 
     */
    public function getInternationalBeforeBoarding()
    {
        return $this->internationalBeforeBoarding;
    }

    /**
     * Set internationalAfterBoarding
     *
     * @param string $internationalAfterBoarding
     * @return RefundRepricing
     */
    public function setInternationalAfterBoarding($internationalAfterBoarding)
    {
        $this->internationalAfterBoarding = $internationalAfterBoarding;

        return $this;
    }

    /**
     * Get internationalAfterBoarding
     *
     * @return string 
     */
    public function getInternationalAfterBoarding()
    {
        return $this->internationalAfterBoarding;
    }

    /**
     * Set southAmericaBeforeBoarding
     *
     * @param string $southAmericaBeforeBoarding
     * @return RefundRepricing
     */
    public function setSouthAmericaBeforeBoarding($southAmericaBeforeBoarding)
    {
        $this->southAmericaBeforeBoarding = $southAmericaBeforeBoarding;

        return $this;
    }

    /**
     * Get southAmericaBeforeBoarding
     *
     * @return string 
     */
    public function getSouthAmericaBeforeBoarding()
    {
        return $this->southAmericaBeforeBoarding;
    }

    /**
     * Set southAmericaAfterBoarding
     *
     * @param string $southAmericaAfterBoarding
     * @return RefundRepricing
     */
    public function setSouthAmericaAfterBoarding($southAmericaAfterBoarding)
    {
        $this->southAmericaAfterBoarding = $southAmericaAfterBoarding;

        return $this;
    }

    /**
     * Get southAmericaAfterBoarding
     *
     * @return string 
     */
    public function getSouthAmericaAfterBoarding()
    {
        return $this->southAmericaAfterBoarding;
    }

    /**
     * Set nationalBeforeBoardingCost
     *
     * @param string $nationalBeforeBoardingCost
     * @return RefundRepricing
     */
    public function setNationalBeforeBoardingCost($nationalBeforeBoardingCost)
    {
        $this->nationalBeforeBoardingCost = $nationalBeforeBoardingCost;

        return $this;
    }

    /**
     * Get nationalBeforeBoardingCost
     *
     * @return string 
     */
    public function getNationalBeforeBoardingCost()
    {
        return $this->nationalBeforeBoardingCost;
    }

    /**
     * Set nationalAfterBoardingCost
     *
     * @param string $nationalAfterBoardingCost
     * @return RefundRepricing
     */
    public function setNationalAfterBoardingCost($nationalAfterBoardingCost)
    {
        $this->nationalAfterBoardingCost = $nationalAfterBoardingCost;

        return $this;
    }

    /**
     * Get nationalAfterBoardingCost
     *
     * @return string 
     */
    public function getNationalAfterBoardingCost()
    {
        return $this->nationalAfterBoardingCost;
    }

    /**
     * Set northAmericaBeforeBoardingCost
     *
     * @param string $northAmericaBeforeBoardingCost
     * @return RefundRepricing
     */
    public function setNorthAmericaBeforeBoardingCost($northAmericaBeforeBoardingCost)
    {
        $this->northAmericaBeforeBoardingCost = $northAmericaBeforeBoardingCost;

        return $this;
    }

    /**
     * Get northAmericaBeforeBoardingCost
     *
     * @return string 
     */
    public function getNorthAmericaBeforeBoardingCost()
    {
        return $this->northAmericaBeforeBoardingCost;
    }

    /**
     * Set northAmericaAfterBoardingCost
     *
     * @param string $northAmericaAfterBoardingCost
     * @return RefundRepricing
     */
    public function setNorthAmericaAfterBoardingCost($northAmericaAfterBoardingCost)
    {
        $this->northAmericaAfterBoardingCost = $northAmericaAfterBoardingCost;

        return $this;
    }

    /**
     * Get northAmericaAfterBoardingCost
     *
     * @return string 
     */
    public function getNorthAmericaAfterBoardingCost()
    {
        return $this->northAmericaAfterBoardingCost;
    }

    /**
     * Set internationalBeforeBoardingCost
     *
     * @param string $internationalBeforeBoardingCost
     * @return RefundRepricing
     */
    public function setInternationalBeforeBoardingCost($internationalBeforeBoardingCost)
    {
        $this->internationalBeforeBoardingCost = $internationalBeforeBoardingCost;

        return $this;
    }

    /**
     * Get internationalBeforeBoardingCost
     *
     * @return string 
     */
    public function getInternationalBeforeBoardingCost()
    {
        return $this->internationalBeforeBoardingCost;
    }

    /**
     * Set internationalAfterBoardingCost
     *
     * @param string $internationalAfterBoardingCost
     * @return RefundRepricing
     */
    public function setInternationalAfterBoardingCost($internationalAfterBoardingCost)
    {
        $this->internationalAfterBoardingCost = $internationalAfterBoardingCost;

        return $this;
    }

    /**
     * Get internationalAfterBoardingCost
     *
     * @return string 
     */
    public function getInternationalAfterBoardingCost()
    {
        return $this->internationalAfterBoardingCost;
    }

    /**
     * Set southAmericaBeforeBoardingCost
     *
     * @param string $southAmericaBeforeBoardingCost
     * @return RefundRepricing
     */
    public function setSouthAmericaBeforeBoardingCost($southAmericaBeforeBoardingCost)
    {
        $this->southAmericaBeforeBoardingCost = $southAmericaBeforeBoardingCost;

        return $this;
    }

    /**
     * Get southAmericaBeforeBoardingCost
     *
     * @return string 
     */
    public function getSouthAmericaBeforeBoardingCost()
    {
        return $this->southAmericaBeforeBoardingCost;
    }

    /**
     * Set southAmericaAfterBoardingCost
     *
     * @param string $southAmericaAfterBoardingCost
     * @return RefundRepricing
     */
    public function setSouthAmericaAfterBoardingCost($southAmericaAfterBoardingCost)
    {
        $this->southAmericaAfterBoardingCost = $southAmericaAfterBoardingCost;

        return $this;
    }

    /**
     * Get southAmericaAfterBoardingCost
     *
     * @return string 
     */
    public function getSouthAmericaAfterBoardingCost()
    {
        return $this->southAmericaAfterBoardingCost;
    }

    /**
     * Set operationPlan
     *
     * @param \AirlineOperationsPlan $operationPlan
     * @return RefundRepricing
     */
    public function setOperationPlan(\AirlineOperationsPlan $operationPlan = null)
    {
        $this->operationPlan = $operationPlan;

        return $this;
    }

    /**
     * Get operationPlan
     *
     * @return \AirlineOperationsPlan 
     */
    public function getOperationPlan()
    {
        return $this->operationPlan;
    }

    /**
     * Set airline
     *
     * @param \Airline $airline
     * @return RefundRepricing
     */
    public function setAirline(\Airline $airline = null)
    {
        $this->airline = $airline;

        return $this;
    }

    /**
     * Get airline
     *
     * @return \Airline 
     */
    public function getAirline()
    {
        return $this->airline;
    }
}
