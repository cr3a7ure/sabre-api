<?php

// src/AppBundle/DataProvider/OfferCollectionDataProvider.php

namespace AppBundle\DataProvider;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Airport;
use AppBundle\Entity\Flight;
use AppBundle\Entity\PostalAddress;
use AppBundle\Entity\GeoCoordinates;
use AppBundle\Action\SabreRetrieveTokenAction as Sabre;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use \DateTime;
use \DateInterval;
use Unirest;
// use Mashape\UnirestPhp\Unirest;

final class OfferCollectionDataProvider implements CollectionDataProviderInterface
{
  protected $requestStack;
  protected $clientId;
  protected $clientSecret;
  protected $token;

  public function __construct(RequestStack $requestStack, $reg, $clientId, $clientSecret)
    {
        $this->requestStack = $requestStack;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->token = Sabre::getSabreToken($clientId,$clientSecret);
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        if (Offer::class !== $resourceClass) {
            throw new ResourceClassNotSupportedException();
        }
        // Get parameters
        $request = $this->requestStack->getCurrentRequest();
        $searchParametersObj = $request->query->all();
        $searchParametersKeys = array_keys($searchParametersObj);
        // dump($request);
        dump($searchParametersObj);
        // dump($searchParametersKeys);
        $searchQuery = [];
        $variable = '';
        foreach ($searchParametersObj as $key => $value) {
            if(is_array($value)){
                // foreach ($value as $i => $arrayValue) {
                //     $chainPropsKey = explode("_", $key);
                //     $propertyKey = end($chainPropsKey);
                //     dump($propertyKey);
                //     $searchQuery[$propertyKey] = $value;
                //     dump($searchQuery[$propertyKey]);
                // }
            } else {
                $chainPropsKey = explode("_", $key);
                $propertyKey = end($chainPropsKey);
                // dump($propertyKey);
                $searchQuery[$propertyKey] = $value;
                // dump($searchQuery[$propertyKey]);
            }
        }

        $now = new  DateTime();
        $interval = new DateInterval('P1W');
        $next_week = $now->add($interval);
        if(array_key_exists('itemOffered_arrivalAirport_iataCode',$searchParametersObj)) {
            $searchQuery['destination'] = $searchParametersObj['itemOffered_arrivalAirport_iataCode'];
        } else {
          $searchQuery['destination'] = 'JFK';
        }
        if (array_key_exists('itemOffered_departureAirport_iataCode',$searchParametersObj)) {
            $searchQuery['origin'] = $searchParametersObj['itemOffered_departureAirport_iataCode'];
        } else {
            $searchQuery['origin'] = 'LAX';
        }
        if (array_key_exists('itemOffered_arrivalTime',$searchParametersObj)) {
            // $da = \DateTime::createFromFormat('Y-d-m\TH:i:s.u',$searchParametersObj['itemOffered_arrivalTime']);
            $da = \DateTime::createFromFormat(\DateTime::W3C,$searchParametersObj['itemOffered_arrivalTime']);
            $searchQuery['returndate'] =  $da->format('Y-m-d');
            dump($da);
        } else {
            $searchQuery['returndate'] = $now->format('Y-m-d');
        }
        if (array_key_exists('itemOffered_departureTime',$searchParametersObj)) {
            // $dd = \DateTime::createFromFormat('Y-d-m\TH:i:s.u',$searchParametersObj['itemOffered_departureTime']);
            // $dd2 = \DateTime::createFromFormat(\DateTime::ATOM,$searchParametersObj['itemOffered_departureTime']);
            $dd = \DateTime::createFromFormat(\DateTime::W3C,$searchParametersObj['itemOffered_departureTime']);
            dump($dd);
            // dump($dd2);
            $searchQuery['departuredate'] =  $dd->format('Y-m-d');
        } else {
            $searchQuery['departuredate'] = $next_week->format('Y-m-d');
        }
        dump($searchQuery);
        $token = $this->token;
        // $token = 'bearer T1RLAQIdFbN195gm3G3AUaUjddM9JW6ulhBqhCWrMS7sFdXh3YrmNzD0AADABpc1DvFHLF6EDyvrDJxxM4ewAT6MCVD6ArSD6xRP/6VC5FewXSi2ZmGd/cRtx/rAL7nMuCH/0HwUZkCQcySIvRs0EZqgTal1aPcQh8WUL0iYZkU/Rrbf0osC5APhcRLOt2kSc25g3iqlppokSrQPG6FDA3VJ9uRAhVnnqETHYlWaH04sREsTkOj3UPRXQ9hZ1m1SWsJ32UnR9WhNaJlv6MBDRdXZXpQ59au5NiH1ecyDuPkDbj0SbcDRjD1xBnK2';

        $headers = array('Authorization' => $token, 'Content-Type' => 'application/json' );
        // $headers = array('X-Originating-Ip' => '94.66.220.69');
        $query = array();
        $query['origin'] = $searchQuery['origin'];//'JFK';
        $query['destination'] = $searchQuery['destination'];//'LAX';
        $query['departuredate'] = $searchQuery['departuredate'];//'2017-07-07';
        $query['returndate'] = $searchQuery['returndate'];//'2017-07-08';
        $query['onlineitinerariesonly'] = 'N';
        $query['limit'] = '10';
        $query['offset'] = '1';
        $query['eticketsonly'] = 'n';
        $query['sortby'] = 'totalfare';
        $query['order'] = 'asc';
        $query['sortby2'] = 'departuretime';
        $query['order2'] = 'asc';
        $query['pointofsalecountry'] = '';

        // dump($query);
        $url = 'https://api.test.sabre.com/v1/shop/flights';
        $response = Unirest\Request::get($url,$headers,$query);

        dump($response);
        if($response->code === 200) {
            if (property_exists($response->body,'status')) {
                if ($response->body->status=='NotProcessed') {
                    return [];
                }
            }
        } else {
            return [];
        }
// +"DepartureDateTime": "2017-07-07T06:30:00"
// +"ArrivalDateTime": "2017-07-07T08:23:00"
        $originAirport = new Airport();
        $originAirport->setId(1);
        $originAirport->setIataCode($query['origin']);
        $destinationAirport = new Airport();
        $destinationAirport->setId(2);
        $destinationAirport->setIataCode($query['destination']);
        $offersArray = $response->body->PricedItineraries;
        $flights = array();
        $offered = array();
        $airlineCodes = array();
        foreach ($offersArray as $key => $value) {
            // dump($destination);
            $flights[$key] = new Flight();
            $flights[$key]->setId($key);
            $flights[$key]->setAdditionalType('https://schema.org/Product');
            $flights[$key]->setArrivalAirport($originAirport);
            $flights[$key]->setDepartureAirport($destinationAirport);
            $departureTime = \DateTime::createFromFormat("Y-m-d?H:i:s",$value->AirItinerary->OriginDestinationOptions->OriginDestinationOption[0]->FlightSegment[0]->DepartureDateTime);
            $arrivalTime = \DateTime::createFromFormat("Y-m-d?H:i:s",$value->AirItinerary->OriginDestinationOptions->OriginDestinationOption[1]->FlightSegment[0]->DepartureDateTime);
            $flights[$key]->setDepartureTime($departureTime);
            $flights[$key]->setArrivalTime($arrivalTime);
            $airlineCode = $value->AirItinerary->OriginDestinationOptions->OriginDestinationOption[0]->FlightSegment[0]->OperatingAirline->Code;
            // if (array_key_exists('CompanyShortName',($value->AirItinerary->OriginDestinationOptions->OriginDestinationOption[0]->FlightSegment[0]->OperatingAirline))) {
            //     $flights[$key]->setProvider($value->AirItinerary->OriginDestinationOptions->OriginDestinationOption[0]->FlightSegment[0]->OperatingAirline->CompanyShortName);
            // } else {
            //     $airlineCodes[$key] = $airlineCode;
            // }
            $airlineCodes[$key] = $airlineCode;
            $offered[$key] = new Offer();
            $offered[$key]->setId($key);
            $offered[$key]->setPrice(floatval($value->AirItineraryPricingInfo->ItinTotalFare->TotalFare->Amount));
            $offered[$key]->setPriceCurrency($value->AirItineraryPricingInfo->ItinTotalFare->TotalFare->CurrencyCode);
            $offered[$key]->setSeller('sabre');
            $offered[$key]->setItemOffered($flights[$key]);
        }

        $airlines = implode(',',$airlineCodes);
        // foreach ($airlineCodes as $key => $value) {
        //     $airlines .= $value . ',';
        // }
        // array_pop($airlines);
        // dump($airlines);
        $url = 'https://api.test.sabre.com/v1/lists/utilities/airlines?airlinecode='.$airlines;
        $airlineResponse = Unirest\Request::get($url,$headers);
        // dump($airlineResponse);
        $airlineData = $airlineResponse->body->AirlineInfo;
        foreach ($airlineCodes as $i => $v) {
            foreach ($airlineData as $k => $value) {
                if($value->AirlineCode==$v) {
                    $flights[$i]->setProvider($value->AirlineName);
                }
            }
        }
        // $value->AirItinerary->DirectionInd 'Return';
        // throw new \Exception('DUMPSTERRRRR!');
            return $offered;
    }
}