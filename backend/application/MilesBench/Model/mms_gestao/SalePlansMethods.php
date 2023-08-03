<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SalePlansMethods
 *
 * @ORM\Table(name="sale_plans_methods")
 * @ORM\Entity
 */
class SalePlansMethods
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
     * @var integer
     *
     * @ORM\Column(name="sale_plans_id", type="integer", nullable=false)
     */
    private $salePlansId;

    /**
     * @var integer
     *
     * @ORM\Column(name="plans_methods_id", type="integer", nullable=false)
     */
    private $plansMethodsId;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;


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
     * Set salePlansId
     *
     * @param integer $salePlansId
     * @return SalePlansMethods
     */
    public function setSalePlansId($salePlansId)
    {
        $this->salePlansId = $salePlansId;

        return $this;
    }

    /**
     * Get salePlansId
     *
     * @return integer 
     */
    public function getSalePlansId()
    {
        return $this->salePlansId;
    }

    /**
     * Set plansMethodsId
     *
     * @param integer $plansMethodsId
     * @return SalePlansMethods
     */
    public function setPlansMethodsId($plansMethodsId)
    {
        $this->plansMethodsId = $plansMethodsId;

        return $this;
    }

    /**
     * Get plansMethodsId
     *
     * @return integer 
     */
    public function getPlansMethodsId()
    {
        return $this->plansMethodsId;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return SalePlansMethods
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }
}
