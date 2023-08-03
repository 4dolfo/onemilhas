<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PlansPromotions
 *
 * @ORM\Table(name="plans_promotions")
 * @ORM\Entity
 */
class PlansPromotions
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
     * @ORM\Column(name="status", type="string", length=5, nullable=false)
     */
    private $status = 'false';

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
     * @ORM\Column(name="url_image", type="string", length=1024, nullable=false)
     */
    private $urlImage;

    /**
     * @var string
     *
     * @ORM\Column(name="plans", type="string", length=1024, nullable=false)
     */
    private $plans;

    /**
     * @var string
     *
     * @ORM\Column(name="airlines", type="string", length=1024, nullable=false)
     */
    private $airlines;


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
     * Set status
     *
     * @param string $status
     * @return PlansPromotions
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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return PlansPromotions
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
     * @return PlansPromotions
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
     * Set urlImage
     *
     * @param string $urlImage
     * @return PlansPromotions
     */
    public function setUrlImage($urlImage)
    {
        $this->urlImage = $urlImage;

        return $this;
    }

    /**
     * Get urlImage
     *
     * @return string 
     */
    public function getUrlImage()
    {
        return $this->urlImage;
    }

    /**
     * Set plans
     *
     * @param string $plans
     * @return PlansPromotions
     */
    public function setPlans($plans)
    {
        $this->plans = $plans;

        return $this;
    }

    /**
     * Get plans
     *
     * @return string 
     */
    public function getPlans()
    {
        return $this->plans;
    }

    /**
     * Set airlines
     *
     * @param string $airlines
     * @return PlansPromotions
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
}
