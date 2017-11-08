<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An offer to transfer some rights to an item or to provide a service — for example, an offer to sell tickets to an event, to rent the DVD of a movie, to stream a TV show over the internet, to repair a motorcycle, or to loan a book.\\n\\nFor \[GTIN\](http://www.gs1.org/barcodes/technical/idkeys/gtin)-related fields, see \[Check Digit calculator\](http://www.gs1.org/barcodes/support/check\_digit\_calculator) and \[validation guide\](http://www.gs1us.org/resources/standards/gtin-validation-guide) from \[GS1\](http://www.gs1.org/).
 *
 * @see http://schema.org/Offer Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(type="http://schema.org/Offer",
 *              iri="http://schema.org/Offer",
 *              attributes={"filters"={"offer.search"},
 *                     "normalization_context"={"groups"={"readOffer"}},
 *                     "denormalization_context"={"groups"={"writeOffer"}},
 *                     "force_eager"=false
 *             },
 *             collectionOperations={
 *                 "get"={"method"="GET", "hydra_context"={"@type"="schema:SearchAction",
 *                                                         "schema:target"="/offers",
 *                                                         "schema:query"={"@type"="vocab:#Flight"},
 *                                                         "schema:result"="vocab:#Offer",
 *                                                         "schema:object"="vocab:#Offer"
 *                                         }},
 *                 "post"={"method"="POST"}
 *             }
 *             )
 */
class Offer
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"readOffer", "writeOffer"})
     */
    private $id;

    /**
     * @var string The currency (in 3-letter ISO 4217 format) of the price or a price component, when attached to \[\[PriceSpecification\]\] and its subtypes
     *
     * @ORM\Column
     * @Assert\Type(type="string")
     * @Assert\NotNull
     * @ApiProperty(iri="http://schema.org/priceCurrency")
     * @Groups({"readOffer", "writeOffer"})
     */
    private $priceCurrency;

    /**
     * @var Flight The item being offered
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Flight")
     * @ApiProperty(iri="http://schema.org/itemOffered")
     * @Groups({"readOffer", "writeOffer"})
     */
    private $itemOffered;

    /**
     * @var float The offer price of a product, or of a price component when attached to PriceSpecification and its subtypes.\\n\\nUsage guidelines:\\n\\n\* Use the \[\[priceCurrency\]\] property (with \[ISO 4217 codes\](http://en.wikipedia.org/wiki/ISO\_4217#Active\_codes) e.g. "USD") instead of including \[ambiguous symbols\](http://en.wikipedia.org/wiki/Dollar\_sign#Currencies\_that\_use\_the\_dollar\_or\_peso\_sign) such as '$' in the value.\\n\* Use '.' (Unicode 'FULL STOP' (U+002E)) rather than ',' to indicate a decimal point. Avoid using these symbols as a readability separator.\\n\* Note that both \[RDFa\](http://www.w3.org/TR/xhtml-rdfa-primer/#using-the-content-attribute) and Microdata syntax allow the use of a "content=" attribute for publishing simple machine-readable values alongside more human-friendly formatting.\\n\* Use values from 0123456789 (Unicode 'DIGIT ZERO' (U+0030) to 'DIGIT NINE' (U+0039)) rather than superficially similiar Unicode symbols
     *
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type(type="float")
     * @ApiProperty(iri="http://schema.org/price")
     * @Groups({"readOffer", "writeOffer"})
     */
    private $price;

    /**
     * @var string An entity which offers (sells / leases / lends / loans) the services / goods. A seller may also be a provider
     *
     * @ORM\Column(nullable=true)
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/seller")
     * @Groups({"readOffer", "writeOffer"})
     */
    private $seller;

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
     * Sets priceCurrency.
     *
     * @param string $priceCurrency
     *
     * @return $this
     */
    public function setPriceCurrency($priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;

        return $this;
    }

    /**
     * Gets priceCurrency.
     *
     * @return string
     */
    public function getPriceCurrency()
    {
        return $this->priceCurrency;
    }

    /**
     * Sets itemOffered.
     *
     * @param Flight $itemOffered
     *
     * @return $this
     */
    public function setItemOffered(Flight $itemOffered = null)
    {
        $this->itemOffered = $itemOffered;

        return $this;
    }

    /**
     * Gets itemOffered.
     *
     * @return Flight
     */
    public function getItemOffered()
    {
        return $this->itemOffered;
    }

    /**
     * Sets price.
     *
     * @param float $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Gets price.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Sets seller.
     *
     * @param string $seller
     *
     * @return $this
     */
    public function setSeller($seller)
    {
        $this->seller = $seller;

        return $this;
    }

    /**
     * Gets seller.
     *
     * @return string
     */
    public function getSeller()
    {
        return $this->seller;
    }

}
