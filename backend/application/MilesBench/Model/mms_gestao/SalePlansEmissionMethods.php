<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SalePlansEmissionMethods
 *
 * @ORM\Table(name="sale_plans_emission_methods", indexes={@ORM\Index(name="sale_plans_id", columns={"sale_plans_id"}), @ORM\Index(name="plans_emission_methods_id", columns={"plans_emission_methods_id"})})
 * @ORM\Entity
 */
class SalePlansEmissionMethods
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
     * @var \SalePlans
     *
     * @ORM\ManyToOne(targetEntity="SalePlans")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sale_plans_id", referencedColumnName="id")
     * })
     */
    private $salePlans;

    /**
     * @var \PlansEmissionMethods
     *
     * @ORM\ManyToOne(targetEntity="PlansEmissionMethods")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plans_emission_methods_id", referencedColumnName="id")
     * })
     */
    private $plansEmissionMethods;


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
     * Set salePlans
     *
     * @param \SalePlans $salePlans
     * @return SalePlansEmissionMethods
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
     * Set plansEmissionMethods
     *
     * @param \PlansEmissionMethods $plansEmissionMethods
     * @return SalePlansEmissionMethods
     */
    public function setPlansEmissionMethods(\PlansEmissionMethods $plansEmissionMethods = null)
    {
        $this->plansEmissionMethods = $plansEmissionMethods;

        return $this;
    }

    /**
     * Get plansEmissionMethods
     *
     * @return \PlansEmissionMethods 
     */
    public function getPlansEmissionMethods()
    {
        return $this->plansEmissionMethods;
    }
}
