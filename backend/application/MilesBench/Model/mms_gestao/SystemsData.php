<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SystemsData
 *
 * @ORM\Table(name="systems_data")
 * @ORM\Entity
 */
class SystemsData
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
     * @ORM\Column(name="system_name", type="string", length=240, nullable=false)
     */
    private $systemName;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=240, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="logo_url", type="integer", nullable=true)
     */
    private $logoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="label_name", type="string", length=240, nullable=true)
     */
    private $labelName;

    /**
     * @var string
     *
     * @ORM\Column(name="label_description", type="string", length=240, nullable=true)
     */
    private $labelDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="label_adress", type="string", length=240, nullable=true)
     */
    private $labelAdress;

    /**
     * @var string
     *
     * @ORM\Column(name="label_phone", type="string", length=240, nullable=true)
     */
    private $labelPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="label_email", type="string", length=240, nullable=true)
     */
    private $labelEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="logo_url_small", type="string", length=240, nullable=true)
     */
    private $logoUrlSmall;


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
     * Set systemName
     *
     * @param string $systemName
     * @return SystemsData
     */
    public function setSystemName($systemName)
    {
        $this->systemName = $systemName;

        return $this;
    }

    /**
     * Get systemName
     *
     * @return string 
     */
    public function getSystemName()
    {
        return $this->systemName;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return SystemsData
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
     * Set logoUrl
     *
     * @param integer $logoUrl
     * @return SystemsData
     */
    public function setLogoUrl($logoUrl)
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    /**
     * Get logoUrl
     *
     * @return integer 
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * Set labelName
     *
     * @param string $labelName
     * @return SystemsData
     */
    public function setLabelName($labelName)
    {
        $this->labelName = $labelName;

        return $this;
    }

    /**
     * Get labelName
     *
     * @return string 
     */
    public function getLabelName()
    {
        return $this->labelName;
    }

    /**
     * Set labelDescription
     *
     * @param string $labelDescription
     * @return SystemsData
     */
    public function setLabelDescription($labelDescription)
    {
        $this->labelDescription = $labelDescription;

        return $this;
    }

    /**
     * Get labelDescription
     *
     * @return string 
     */
    public function getLabelDescription()
    {
        return $this->labelDescription;
    }

    /**
     * Set labelAdress
     *
     * @param string $labelAdress
     * @return SystemsData
     */
    public function setLabelAdress($labelAdress)
    {
        $this->labelAdress = $labelAdress;

        return $this;
    }

    /**
     * Get labelAdress
     *
     * @return string 
     */
    public function getLabelAdress()
    {
        return $this->labelAdress;
    }

    /**
     * Set labelPhone
     *
     * @param string $labelPhone
     * @return SystemsData
     */
    public function setLabelPhone($labelPhone)
    {
        $this->labelPhone = $labelPhone;

        return $this;
    }

    /**
     * Get labelPhone
     *
     * @return string 
     */
    public function getLabelPhone()
    {
        return $this->labelPhone;
    }

    /**
     * Set labelEmail
     *
     * @param string $labelEmail
     * @return SystemsData
     */
    public function setLabelEmail($labelEmail)
    {
        $this->labelEmail = $labelEmail;

        return $this;
    }

    /**
     * Get labelEmail
     *
     * @return string 
     */
    public function getLabelEmail()
    {
        return $this->labelEmail;
    }

    /**
     * Set logoUrlSmall
     *
     * @param string $logoUrlSmall
     * @return SystemsData
     */
    public function setLogoUrlSmall($logoUrlSmall)
    {
        $this->logoUrlSmall = $logoUrlSmall;

        return $this;
    }

    /**
     * Get logoUrlSmall
     *
     * @return string 
     */
    public function getLogoUrlSmall()
    {
        return $this->logoUrlSmall;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="emission_term", type="string", length=10200, nullable=true)
     */
    private $emissionTerm;

    /**
     * @var string
     *
     * @ORM\Column(name="conclusion_term", type="string", length=6200, nullable=true)
     */
    private $conclusionTerm;


    /**
     * Set emissionTerm
     *
     * @param string $emissionTerm
     * @return SystemsData
     */
    public function setEmissionTerm($emissionTerm)
    {
        $this->emissionTerm = $emissionTerm;

        return $this;
    }

    /**
     * Get emissionTerm
     *
     * @return string 
     */
    public function getEmissionTerm()
    {
        return $this->emissionTerm;
    }

    /**
     * Set conclusionTerm
     *
     * @param string $conclusionTerm
     * @return SystemsData
     */
    public function setConclusionTerm($conclusionTerm)
    {
        $this->conclusionTerm = $conclusionTerm;

        return $this;
    }

    /**
     * Get conclusionTerm
     *
     * @return string 
     */
    public function getConclusionTerm()
    {
        return $this->conclusionTerm;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=50, nullable=true)
     */
    private $color;

    /**
     * @var string
     *
     * @ORM\Column(name="color_2", type="string", length=50, nullable=true)
     */
    private $color2;

    /**
     * @var string
     *
     * @ORM\Column(name="url_math", type="string", length=150, nullable=true)
     */
    private $urlMath;


    /**
     * Set color
     *
     * @param string $color
     * @return SystemsData
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set color2
     *
     * @param string $color2
     * @return SystemsData
     */
    public function setColor2($color2)
    {
        $this->color2 = $color2;

        return $this;
    }

    /**
     * Get color2
     *
     * @return string 
     */
    public function getColor2()
    {
        return $this->color2;
    }

    /**
     * Set urlMath
     *
     * @param string $urlMath
     * @return SystemsData
     */
    public function setUrlMath($urlMath)
    {
        $this->urlMath = $urlMath;

        return $this;
    }

    /**
     * Get urlMath
     *
     * @return string 
     */
    public function getUrlMath()
    {
        return $this->urlMath;
    }
}
