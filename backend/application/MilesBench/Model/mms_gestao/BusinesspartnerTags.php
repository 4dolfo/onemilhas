<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * BusinesspartnerTags
 *
 * @ORM\Table(name="businesspartner_tags", indexes={@ORM\Index(name="tag_id", columns={"tag_id"}), @ORM\Index(name="businesspartner_id", columns={"businesspartner_id"})})
 * @ORM\Entity
 */
class BusinesspartnerTags
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
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="businesspartner_id", referencedColumnName="id")
     * })
     */
    private $businesspartner;

    /**
     * @var \Tags
     *
     * @ORM\ManyToOne(targetEntity="Tags")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     * })
     */
    private $tag;


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
     * Set businesspartner
     *
     * @param \Businesspartner $businesspartner
     * @return BusinesspartnerTags
     */
    public function setBusinesspartner(\Businesspartner $businesspartner = null)
    {
        $this->businesspartner = $businesspartner;

        return $this;
    }

    /**
     * Get businesspartner
     *
     * @return \Businesspartner 
     */
    public function getBusinesspartner()
    {
        return $this->businesspartner;
    }

    /**
     * Set tag
     *
     * @param \Tags $tag
     * @return BusinesspartnerTags
     */
    public function setTag(\Tags $tag = null)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return \Tags 
     */
    public function getTag()
    {
        return $this->tag;
    }
}
