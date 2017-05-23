<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A hotel is an establishment that provides lodging paid on a short-term basis (Source: Wikipedia, the free encyclopedia, see http://en.wikipedia.org/wiki/Hotel).
 *
 * See also the [dedicated document on the use of schema.org for marking up hotels and other forms of accommodations](/docs/hotels.html).
 *
 * @see http://schema.org/Hotel Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(type="http://schema.org/Hotel",
 *             iri="http://schema.org/Hotel",
 *             attributes={"filters"={"hotel.search"},
 *                     "normalization_context"={"groups"={"readHotel"}},
 *                     "denormalization_context"={"groups"={"writeHotel"}}
 *             },
 *             collectionOperations={
 *                 "get"={"method"="GET",
 *                        "hydra_context"={"@type"="schema:searchAction",
 *                                         "target"="/hotels",
 *                                         "query"={"@type"="vocab:#GeoCoordinates"},
 *                                         "result"="vocab:#Hotel",
 *                                         "object"="vocab:#Hotel"
 *                                         }},
 *                 "post"={"method"="POST"}
 *             }
 *             )
 */
class Hotel
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"readHotel", "writeHotel"})
     */
    private $id;

    /**
     * @var string The name of the item
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/name")
     * @Groups({"readHotel", "writeHotel"})
     */
    private $name;

    /**
     * @var string A description of the item
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/description")
     * @Groups({"readHotel", "writeHotel"})
     */
    private $description;

    /**
     * @var PostalAddress Physical address of the item
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\PostalAddress")
     * @ApiProperty(iri="http://schema.org/address")
     * @Groups({"readHotel", "writeHotel"})
     */
    private $address;

    /**
     * @var string The fax number
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/faxNumber")
     * @Groups({"readHotel", "writeHotel"})
     */
    private $faxNumber;

    /**
     * @var string The telephone number
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/telephone")
     * @Groups({"readHotel", "writeHotel"})
     */
    private $telephone;

    /**
     * @var GeoCoordinates
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\GeoCoordinates")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull
     * @ApiProperty(iri="http://schema.org/geo")
     * @Groups({"readHotel", "writeHotel"})
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
     * Sets name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * Sets faxNumber.
     *
     * @param string $faxNumber
     *
     * @return $this
     */
    public function setFaxNumber($faxNumber)
    {
        $this->faxNumber = $faxNumber;

        return $this;
    }

    /**
     * Gets faxNumber.
     *
     * @return string
     */
    public function getFaxNumber()
    {
        return $this->faxNumber;
    }

    /**
     * Sets telephone.
     *
     * @param string $telephone
     *
     * @return $this
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Gets telephone.
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
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
