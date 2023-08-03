<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SalePlans
 *
 * @ORM\Table(name="sale_plans", indexes={@ORM\Index(name="plan_user_id", columns={"plan_user_id"})})
 * @ORM\Entity
 */
class SalePlans
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
     * @ORM\Column(name="name", type="string", length=30, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=200, nullable=true)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_miles", type="boolean", nullable=true)
     */
    private $showMiles;

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_conventional", type="boolean", nullable=true)
     */
    private $showConventional;

    /**
     * @var \PlansUsers
     *
     * @ORM\ManyToOne(targetEntity="PlansUsers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plan_user_id", referencedColumnName="id")
     * })
     */
    private $planUser;


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
     * @return SalePlans
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
     * Set description
     *
     * @param string $description
     * @return SalePlans
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set showMiles
     *
     * @param boolean $showMiles
     * @return SalePlans
     */
    public function setShowMiles($showMiles)
    {
        $this->showMiles = $showMiles;

        return $this;
    }

    /**
     * Get showMiles
     *
     * @return boolean 
     */
    public function getShowMiles()
    {
        return $this->showMiles;
    }

    /**
     * Set showConventional
     *
     * @param boolean $showConventional
     * @return SalePlans
     */
    public function setShowConventional($showConventional)
    {
        $this->showConventional = $showConventional;

        return $this;
    }

    /**
     * Get showConventional
     *
     * @return boolean 
     */
    public function getShowConventional()
    {
        return $this->showConventional;
    }

    /**
     * Set planUser
     *
     * @param \PlansUsers $planUser
     * @return SalePlans
     */
    public function setPlanUser(\PlansUsers $planUser = null)
    {
        $this->planUser = $planUser;

        return $this;
    }

    /**
     * Get planUser
     *
     * @return \PlansUsers 
     */
    public function getPlanUser()
    {
        return $this->planUser;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="referencia", type="string", length=255, nullable=true)
     */
    private $referencia;


    /**
     * Set referencia
     *
     * @param string $referencia
     * @return SalePlans
     */
    public function setReferencia($referencia)
    {
        $this->referencia = $referencia;

        return $this;
    }

    /**
     * Get referencia
     *
     * @return string 
     */
    public function getReferencia()
    {
        return $this->referencia;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="documentos", type="string", length=5, nullable=true)
     */
    private $documentos = 'true';


    /**
     * Set documentos
     *
     * @param string $documentos
     * @return SalePlans
     */
    public function setDocumentos($documentos)
    {
        $this->documentos = $documentos;

        return $this;
    }

    /**
     * Get documentos
     *
     * @return string 
     */
    public function getDocumentos()
    {
        return $this->documentos;
    }
    
    /**
     * @var string
     *
     * @ORM\Column(name="sistema_display", type="string", length=6, nullable=true)
     */
    private $sistema_display = 'true';
    /**
     * Set sistema_display
     *
     * @param string $sistema_display
     * @return SalePlans
     */
    public function setSistemaDisplay($sistema_display)
    {
        $this->sistema_display = $sistema_display;

        return $this;
    }

    /**
     * Get sistema_display
     *
     * @return string 
     */
    public function getSistemaDisplay()
    {
        return $this->sistema_display;
    }


}
