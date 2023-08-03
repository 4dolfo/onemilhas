<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PurchaseBillspay
 *
 * @ORM\Table(name="purchase_billspay", indexes={@ORM\Index(name="fk_purchase_billspay_purchase1", columns={"purchase_id"}), @ORM\Index(name="fk_purchase_billspay_bills1", columns={"billspay_id"})})
 * @ORM\Entity
 */
class PurchaseBillspay
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
     * @var \Purchase
     *
     * @ORM\ManyToOne(targetEntity="Purchase")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_id", referencedColumnName="id")
     * })
     */
    private $purchase;


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
     * @return PurchaseBillspay
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
     * Set purchase
     *
     * @param \Purchase $purchase
     * @return PurchaseBillspay
     */
    public function setPurchase(\Purchase $purchase = null)
    {
        $this->purchase = $purchase;

        return $this;
    }

    /**
     * Get purchase
     *
     * @return \Purchase 
     */
    public function getPurchase()
    {
        return $this->purchase;
    }
}
