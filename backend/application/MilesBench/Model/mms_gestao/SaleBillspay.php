<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SaleBillspay
 *
 * @ORM\Table(name="sale_billspay", indexes={@ORM\Index(name="fk_sale_billspay_sale1", columns={"sale_id"}), @ORM\Index(name="fk_sale_billspay_bills1", columns={"billspay_id"})})
 * @ORM\Entity
 */
class SaleBillspay
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
     * @var \Billspay
     *
     * @ORM\ManyToOne(targetEntity="Billspay")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="billspay_id", referencedColumnName="id")
     * })
     */
    private $billspay;

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
     * Set billspay
     *
     * @param \Billspay $billspay
     * @return SaleBillspay
     */
    public function setBillspay(\Billspay $billspay = null)
    {
        $this->billspay = $billspay;

        return $this;
    }

    /**
     * Get billspay
     *
     * @return \Billspay 
     */
    public function getBillspay()
    {
        return $this->billspay;
    }

    /**
     * Set sale
     *
     * @param \Sale $sale
     * @return SaleBillspay
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
