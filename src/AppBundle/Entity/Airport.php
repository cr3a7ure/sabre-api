<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An airport.
 *
 * @see http://schema.org/Airport Documentation on Schema.org
 *
 * @ORM\Entity
 * @UniqueEntity("iataCode")
 * @ApiResource(type="http://schema.org/Airport",
 *              iri="http://schema.org/Airport",
 *              attributes={"filters"={"airport.search"},
 *                     "normalization_context"={"groups"={"readAirport"}},
 *                     "denormalization_context"={"groups"={"writeAirport"}}
 *             },
 *             collectionOperations={
 *                 "get"={"method"="GET",
 *                        "hydra_context"={"@type"="schema:searchAction",
 *                                         "target"="/airports",
 *                                         "query"={"@type"="vocab:#PostalAddress"},
 *                                         "result"="vocab:#Airport",
 *                                         "object"="vocab:#Airport"
 *                                         }},
 *                 "post"={"method"="POST"}
 *             }
 *             )
 */
class Airport
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
     * @var string IATA identifier for an airline or airport
     *
     * @ORM\Column(nullable=true, unique=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/iataCode")
     * @Groups({"readOffer", "writeOffer","readAirport"})
     */
    private $iataCode;

    /**
     * @var PostalAddress Physical address of the item
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PostalAddress")
     * @ApiProperty(iri="http://schema.org/address")
     * @Groups({"readOffer", "writeOffer","readAirport"})
     */
    private $address;

    /**
     * @var GeoCoordinates
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\GeoCoordinates")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @ApiProperty(iri="http://schema.org/geo")
     * @Groups({"readOffer", "writeOffer","readAirport"})
     */
    private $geo;

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
     * Sets iataCode.
     *
     * @param string $iataCode
     *
     * @return $this
     */
    public function setIataCode($iataCode)
    {
        $this->iataCode = $iataCode;

        return $this;
    }

    /**
     * Gets iataCode.
     *
     * @return string
     */
    public function getIataCode()
    {
        return $this->iataCode;
    }

    /**
     * Sets address.
     *
     * @param PostalAddress $address
     *
     * @return $this
     */
    public function setAddress(PostalAddress $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Gets address.
     *
     * @return PostalAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets geo.
     *
     * @param GeoCoordinates $geo
     *
     * @return $this
     */
    public function setGeo(GeoCoordinates $geo)
    {
        $this->geo = $geo;

        return $this;
    }

    /**
     * Gets geo.
     *
     * @return GeoCoordinates
     */
    public function getGeo()
    {
        return $this->geo;
    }
}
