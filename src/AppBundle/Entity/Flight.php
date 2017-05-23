<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An airline flight.
 *
 * @see http://schema.org/Flight Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(type="http://schema.org/Flight",
 *              iri="http://schema.org/Flight",
 *              attributes={"filters"={"flight.search"}}
 *              )
 */
class Flight
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string An additional type for the item, typically used for adding more specific types from external vocabularies in microdata syntax. This is a relationship between something and a class that the thing is in. In RDFa syntax, it is better to use the native RDFa syntax - the 'typeof' attribute - for multiple types. Schema.org tools may have only weaker understanding of extra types, in particular those defined externally
     *
     * @ORM\Column(nullable=true)
     * @Assert\Url
     * @ApiProperty(iri="http://schema.org/additionalType")
     * @Groups({"readOffer", "writeOffer"})
     */
    private $additionalType;

    /**
     * @var Airport
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Airport")
     * @ApiProperty(iri="http://schema.org/arrivalAirport")
     * @Groups({"readOffer", "writeOffer"})
     */
    private $arrivalAirport;

    /**
     * @var Airport The airport where the flight originates
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Airport")
     * @ApiProperty(iri="http://schema.org/departureAirport")
     * @Groups({"readOffer", "writeOffer"})
     */
    private $departureAirport;

    /**
     * @var \DateTime The expected departure time
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime
     * @ApiProperty(iri="http://schema.org/departureTime")
     * @Groups({"readOffer", "writeOffer"})
     */
    private $departureTime;

    /**
     * @var \DateTime The expected arrival time
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime
     * @ApiProperty(iri="http://schema.org/arrivalTime")
     * @Groups({"readOffer", "writeOffer"})
     */
    private $arrivalTime;

    /**
     * @var string The service provider, service operator, or service performer; the goods producer. Another party (a seller) may offer those services or goods on behalf of the provider. A provider may also serve as the seller
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/provider")
     * @Groups({"readOffer", "writeOffer"})
     */
    private $provider;

    /**
     * Sets id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets additionalType.
     *
     * @param string $additionalType
     *
     * @return $this
     */
    public function setAdditionalType($additionalType)
    {
        $this->additionalType = $additionalType;

        return $this;
    }

    /**
     * Gets additionalType.
     *
     * @return string
     */
    public function getAdditionalType()
    {
        return $this->additionalType;
    }

    /**
     * Sets arrivalAirport.
     *
     * @param Airport $arrivalAirport
     *
     * @return $this
     */
    public function setArrivalAirport(Airport $arrivalAirport)
    {
        $this->arrivalAirport = $arrivalAirport;

        return $this;
    }

    /**
     * Gets arrivalAirport.
     *
     * @return Airport
     */
    public function getArrivalAirport()
    {
        return $this->arrivalAirport;
    }

    /**
     * Sets departureAirport.
     *
     * @param Airport $departureAirport
     *
     * @return $this
     */
    public function setDepartureAirport(Airport $departureAirport = null)
    {
        $this->departureAirport = $departureAirport;

        return $this;
    }

    /**
     * Gets departureAirport.
     *
     * @return Airport
     */
    public function getDepartureAirport()
    {
        return $this->departureAirport;
    }

    /**
     * Sets departureTime.
     *
     * @param \DateTime $departureTime
     *
     * @return $this
     */
    public function setDepartureTime(\DateTime $departureTime = null)
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    /**
     * Gets departureTime.
     *
     * @return \DateTime
     */
    public function getDepartureTime()
    {
        return $this->departureTime;
    }

    /**
     * Sets arrivalTime.
     *
     * @param \DateTime $arrivalTime
     *
     * @return $this
     */
    public function setArrivalTime(\DateTime $arrivalTime = null)
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }

    /**
     * Gets arrivalTime.
     *
     * @return \DateTime
     */
    public function getArrivalTime()
    {
        return $this->arrivalTime;
    }

    /**
     * Sets provider.
     *
     * @param string $provider
     *
     * @return $this
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Gets provider.
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
