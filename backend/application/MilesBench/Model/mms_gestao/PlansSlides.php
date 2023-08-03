<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PlansSlides
 *
 * @ORM\Table(name="plans_slides", indexes={@ORM\Index(name="sale_plans_id", columns={"sale_plans_id"})})
 * @ORM\Entity
 */
class PlansSlides
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
     * @ORM\Column(name="url", type="string", length=512, nullable=false)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=16, nullable=false)
     */
    private $type;

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
     * Set url
     *
     * @param string $url
     * @return PlansSlides
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return PlansSlides
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
     * Set salePlans
     *
     * @param \SalePlans $salePlans
     * @return PlansSlides
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
}
