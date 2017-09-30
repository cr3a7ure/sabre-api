<?php

// src/AppBundle/DataProvider/FlightCollectionDataProvider.php

namespace AppBundle\DataProvider;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Airport;
use AppBundle\Entity\Flight;
use AppBundle\Entity\PostalAddress;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use Unirest;
// use Mashape\UnirestPhp\Unirest;

final class FlightCollectionDataProvider implements CollectionDataProviderInterface
{
  protected $requestStack;

  public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        if (Flight::class !== $resourceClass) {
            throw new ResourceClassNotSupportedException();
        }
        //Temp Objects


        $request = $this->requestStack->getCurrentRequest();
        // $test = $request->query;//->get('');
        $props = $request->query->all();
        $propKeys = array_keys($props);
        // dump($propKeys[1]); 
        // dump($props[$propKeys[1]]);
        $headers = array('Authorization' => 'Bearer T1RLAQIZnn+eMPT5dOCMfWv3st7rsBy9GhBEfXQaxDnZ6vGYH90ZxjQLAADAcbL68pAcRYLt2g4vYAOuKv9xzPaZcrBABn/WSPE5vy4jAZg5LlRv/ISUZgiAsaqlf5rhfXqb5jTWTan4h2eopvtGEXHopsaczDhPj2LH48H1XS2LaKoPCTF3QuFA3cppFA93OzZeSkgdtmLZte9GyERKOWoMuFMeiK2dl3ISySy9y5tCLYPz7h/x00tAo9KqBOa4cjy6tgPHJAE5V2k+N6oVvVhT/vzgbiczxwEiRlxnWXGg5hApTPQrXdvf+WsP');
        $headers = array('Content-Type' => 'application/json');
        $headers = array('X-Originating-Ip' => '94.66.220.69');
        $query = array();
        $query['GeoSearchRQ'] = array();
        $query['GeoSearchRQ']['version'] = 1;
        $query['GeoSearchRQ']['GeoRef'] = array();
        $query['GeoSearchRQ']['GeoRef']['GeoCode'] = array();
        $query['GeoSearchRQ']['GeoRef']['GeoCode']['Latitude'] = 1;
        $query['GeoSearchRQ']['GeoRef']['GeoCode']['Longitude'] = 1;
        $query['GeoSearchRQ']['GeoRef']['Category'] = 'HOTEL';
        $query['GeoSearchRQ']['GeoRef']['Radius'] = 50;
        $query['GeoSearchRQ']['GeoRef']['UOM'] = 'KM';
        dump($query);
        // Skyscanner variables
        $marketCountry = 'GR';
        $currency = 'EUR';
        $locale = 'en-US';
        $originPlace = 'LGW';
        $destinationPlace = 'ATH';
        $outboundPartialDate = '2017-06';
        $inboundPartialDate = '';
        $url = 'https://api.test.sabre.com/v1.0.0/lists/utilities/geosearch/locations?mode=geosearch';

    //     $em = $this->getDoctrine()->getManager();

    // // tells Doctrine you want to (eventually) save the Product (no queries yet)
    //     $em->persist($product);

    // // actually executes the queries (i.e. the INSERT query)
    //     $em->flush();

        // if ((isset($props['departureAirport'])) && (isset($props['departureTime']))&& (isset($props['arrivalAirport']))&& (isset($props['arrivalTime']))) {
            // $originPlace = $props['departureAirport']);
            // $destinationPlace = $props['arrivalAirport'];
            // $outboundPartialDate = $props['departureTime'];
            // $inboundPartialDate = $props['arrivalTime'];

            $response = Unirest\Request::post($url,$headers,$query);
            dump($response);
            $Quotes = $response->body->Quotes;
            $Places = $response->body->Places;
            // dump($Places);
            $Carriers = $response->body->Carriers;
            $carriers =  array();
            foreach ($Carriers as $key => $value) {
                $carriers[$value->CarrierId] = $value->Name;
            }
            $places = array();
            $places2airports = array();
            $addresses = array();
            $airports = array();
            foreach ($Places as $key => $value) {
                $places[$value->PlaceId] =  array();
                if (array_key_exists('IataCode', get_object_vars($value))) {
                    $addresses[$key] = new PostalAddress();
                    $addresses[$key]->setAddressCountry('GR');
                    $addresses[$key]->setAddressLocality($value->CityName);
                    $addresses[$key]->setAddressRegion("kjhkj");
                    $addresses[$key]->setPostalCode("82100");
                    $addresses[$key]->setStreetAddress('sdf');
                    $airports[$key] = new Airport();
                    $airports[$key]->setIataCode($value->IataCode);
                    $airports[$key]->setId($key);
                    $airports[$key]->setAddress($addresses[$key]);
                    $places[$value->PlaceId]['airport'] = $key;
                }
                foreach (get_object_vars($value) as $key2 => $value2) {
                    $places[$value->PlaceId][$key2] = $value2;
                }
            }

            $flights = array();
            $offered = array();
            // throw new \Exception('DUMPSTERRRRR!');
            $Currencies = $response->body->Currencies;
            $defCurr = $Currencies[0]->Code;
            foreach ($Quotes as $key => $value) {
                // dump($value->OutboundLeg->OriginId);
                // dump($places[$value->OutboundLeg->OriginId]['airport']);
                $origin = $airports[$places[$value->OutboundLeg->OriginId]['airport']];
                $destination = $airports[$places[$value->OutboundLeg->DestinationId]['airport']];
                $flights[$key] = new Flight();
                $flights[$key]->setId($key);
                $flights[$key]->setArrivalAirport($destination);
                $flights[$key]->setDepartureAirport($origin);
                $departureTime = \DateTime::createFromFormat("Y-m-d?H:i:s",$value->QuoteDateTime);
                $arrivalTime = \DateTime::createFromFormat("Y-m-d?H:i:s",$value->QuoteDateTime);
                $flights[$key]->setDepartureTime($departureTime);
                $flights[$key]->setArrivalTime($arrivalTime);
                $provider = $value->OutboundLeg->CarrierIds; //Array
                $flights[$key]->setProvider($carriers[$provider[0]]);
                $offered[$key] = new Offer();
                $offered[$key]->setId($key);
                $offered[$key]->setPrice($value->MinPrice);
                $offered[$key]->setPriceCurrency($defCurr);
                $offered[$key]->setSeller($defCurr);
                // $offered[$key]->setItemOffered($flights[$key]);
                // $flights[$key]->setOffers($offered[$key]);
            }

        // $variable = 'Chios';
        // $url = 'https://airports.p.mashape.com/';
        // $headers = array("X-Mashape-Key" => "YFdPBwiJGdmshYQtQEreC8qJuRphp1LOHRejsn2hVY3OdMBnf0",
        //                 "Content-Type" => "application/json",
        //                 "Accept" => "application/json");
        // $query = "{\"search\":\"".$variable."\"}";

        // $response = Unirest\Request::post($url,$headers,$query);
        // dump($response);

            return $flights;
    }
}