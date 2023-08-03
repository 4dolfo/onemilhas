<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CommercialDocuments
 *
 * @ORM\Table(name="commercial_documents")
 * @ORM\Entity
 */
class CommercialDocuments
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
     * @ORM\Column(name="name_file", type="string", length=100, nullable=true)
     */
    private $nameFile;

    /**
     * @var string
     *
     * @ORM\Column(name="type_file", type="string", length=100, nullable=true)
     */
    private $typeFile;

    /**
     * @var string
     *
     * @ORM\Column(name="tag_bucket", type="string", length=100, nullable=true)
     */
    private $tagBucket;


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
     * Set nameFile
     *
     * @param string $nameFile
     * @return CommercialDocuments
     */
    public function setNameFile($nameFile)
    {
        $this->nameFile = $nameFile;

        return $this;
    }

    /**
     * Get nameFile
     *
     * @return string 
     */
    public function getNameFile()
    {
        return $this->nameFile;
    }

    /**
     * Set typeFile
     *
     * @param string $typeFile
     * @return CommercialDocuments
     */
    public function setTypeFile($typeFile)
    {
        $this->typeFile = $typeFile;

        return $this;
    }

    /**
     * Get typeFile
     *
     * @return string 
     */
    public function getTypeFile()
    {
        return $this->typeFile;
    }

    /**
     * Set tagBucket
     *
     * @param string $tagBucket
     * @return CommercialDocuments
     */
    public function setTagBucket($tagBucket)
    {
        $this->tagBucket = $tagBucket;

        return $this;
    }

    /**
     * Get tagBucket
     *
     * @return string 
     */
    public function getTagBucket()
    {
        return $this->tagBucket;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="client_registration_code", type="string", length=20, nullable=true)
     */
    private $clientRegistrationCode;


    /**
     * Set clientRegistrationCode
     *
     * @param string $clientRegistrationCode
     * @return CommercialDocuments
     */
    public function setClientRegistrationCode($clientRegistrationCode)
    {
        $this->clientRegistrationCode = $clientRegistrationCode;

        return $this;
    }

    /**
     * Get clientRegistrationCode
     *
     * @return string 
     */
    public function getClientRegistrationCode()
    {
        return $this->clientRegistrationCode;
    }
}
