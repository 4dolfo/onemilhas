<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SalePlansChargingMethods
 *
 * @ORM\Table(name="sale_plans_charging_methods", indexes={@ORM\Index(name="sale_plans_id", columns={"sale_plans_id"}), @ORM\Index(name="sale_plans_id_2", columns={"sale_plans_id"}), @ORM\Index(name="fk_charging_methods_sale_plans_id", columns={"plans_charging_methods_id"})})
 * @ORM\Entity
 */
class SalePlansChargingMethods
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
     * @var float
     *
     * @ORM\Column(name="interest_free_installment", type="float", precision=20, scale=6, nullable=true)
     */
    private $interestFreeInstallment;

    /**
     * @var integer
     *
     * @ORM\Column(name="interest_free", type="integer", nullable=true)
     */
    private $interestFree;

    /**
     * @var \PlansChargingMethods
     *
     * @ORM\ManyToOne(targetEntity="PlansChargingMethods")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plans_charging_methods_id", referencedColumnName="id")
     * })
     */
    private $plansChargingMethods;

    /**
     * @var \SalePlans
     *
     * @ORM\ManyToOne(targetEntity="SalePlans")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sale_plans_id", referencedColumnName="id")
     * })
     */
    private $salePlans;


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
     * Set interestFreeInstallment
     *
     * @param float $interestFreeInstallment
     * @return SalePlansChargingMethods
     */
    public function setInterestFreeInstallment($interestFreeInstallment)
    {
        $this->interestFreeInstallment = $interestFreeInstallment;

        return $this;
    }

    /**
     * Get interestFreeInstallment
     *
     * @return float 
     */
    public function getInterestFreeInstallment()
    {
        return $this->interestFreeInstallment;
    }

    /**
     * Set interestFree
     *
     * @param integer $interestFree
     * @return SalePlansChargingMethods
     */
    public function setInterestFree($interestFree)
    {
        $this->interestFree = $interestFree;

        return $this;
    }

    /**
     * Get interestFree
     *
     * @return integer 
     */
    public function getInterestFree()
    {
        return $this->interestFree;
    }

    /**
     * Set plansChargingMethods
     *
     * @param \PlansChargingMethods $plansChargingMethods
     * @return SalePlansChargingMethods
     */
    public function setPlansChargingMethods(\PlansChargingMethods $plansChargingMethods = null)
    {
        $this->plansChargingMethods = $plansChargingMethods;

        return $this;
    }

    /**
     * Get plansChargingMethods
     *
     * @return \PlansChargingMethods 
     */
    public function getPlansChargingMethods()
    {
        return $this->plansChargingMethods;
    }

    /**
     * Set salePlans
     *
     * @param \SalePlans $salePlans
     * @return SalePlansChargingMethods
     */
    public function setSalePlans(\SalePlans $salePlans = null)
    {
        $this->salePlans = $salePlans;

        return $this;
    }

    /**
     * Get salePlans
     *
     * @return \SalePlans 
     */
    public function getSalePlans()
    {
        return $this->salePlans;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="extra_value", type="float", precision=20, scale=2, nullable=true)
     */
    private $extraValue = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="extra_type", type="string", length=1, nullable=true)
     */
    private $extraType = 'D';


    /**
     * Set extraValue
     *
     * @param float $extraValue
     * @return SalePlansChargingMethods
     */
    public function setExtraValue($extraValue)
    {
        $this->extraValue = $extraValue;

        return $this;
    }

    /**
     * Get extraValue
     *
     * @return float 
     */
    public function getExtraValue()
    {
        return $this->extraValue;
    }

    /**
     * Set extraType
     *
     * @param string $extraType
     * @return SalePlansChargingMethods
     */
    public function setExtraType($extraType)
    {
        $this->extraType = $extraType;

        return $this;
    }

    /**
     * Get extraType
     *
     * @return string 
     */
    public function getExtraType()
    {
        return $this->extraType;
    }
}
