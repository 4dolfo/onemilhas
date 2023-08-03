<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SaleBillsreceive
 *
 * @ORM\Table(name="sale_billsreceive", indexes={@ORM\Index(name="fk_sale_billsreceive_sale1", columns={"sale_id"}), @ORM\Index(name="fk_sale_billsreceive_bills1", columns={"billsreceive_id"})})
 * @ORM\Entity
 */
class SaleBillsreceive
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
     * @var \Billsreceive
     *
     * @ORM\ManyToOne(targetEntity="Billsreceive")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="billsreceive_id", referencedColumnName="id")
     * })
     */
    private $billsreceive;

    /**
     * @var \Sale
     *
     * @ORM\ManyToOne(targetEntity="Sale")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sale_id", referencedColumnName="id")
     * })
     */
    private $sale;


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
     * Set billsreceive
     *
     * @param \Billsreceive $billsreceive
     * @return SaleBillsreceive
     */
    public function setBillsreceive(\Billsreceive $billsreceive = null)
    {
        $this->billsreceive = $billsreceive;

        return $this;
    }

    /**
     * Get billsreceive
     *
     * @return \Billsreceive 
     */
    public function getBillsreceive()
    {
        return $this->billsreceive;
    }

    /**
     * Set sale
     *
     * @param \Sale $sale
     * @return SaleBillsreceive
     */
    public function setSale(\Sale $sale = null)
    {
        $this->sale = $sale;

        return $this;
    }

    /**
     * Get sale
     *
     * @return \Sale 
     */
    public function getSale()
    {
        return $this->sale;
    }
}
