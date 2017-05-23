<?php

// src/AppBundle/DataProvider/OfferCollectionDataProvider.php

namespace AppBundle\DataProvider;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Airport;
use AppBundle\Entity\Flight;
use AppBundle\Entity\Action;
use AppBundle\Entity\PostalAddress;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use Unirest;
// use Mashape\UnirestPhp\Unirest;

final class OfferCollectionDataProvider implements CollectionDataProviderInterface
{
  protected $requestStack;

  public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    protected function getSabreToken() {
        $clientId = 'V1:2hxuxcm6hz4aex5n:DEVCENTER:EXT';
        $encodedId = base64_encode($clientId);
        $clientSecret = 'NhATlh81';
        $encodedSecret = base64_encode($clientSecret);
        $id = base64_encode($encodedId.':'.$encodedSecret);
        $urlToken = 'https://api.sabre.com/v2/auth/token';
        $urlToken = 'https://api.test.sabre.com/v2/auth/token';
        $headersToken= array('Authorization' => 'Basic '.$id, 'Content-Type' => 'application/x-www-form-urlencoded', 'Accept' => '*/*' );
        dump($headersToken);
        $payloadArray = array('grant_type'=>'client_credentials');
        $payload = Unirest\Request\Body::json($payloadArray);
        dump($payload);
        $payload = 'grant_type=client_credentials';
        dump($payloadArray);
        $responseToken = Unirest\Request::post($urlToken,$headersToken,$payload);
        $token = $responseToken->body->token_type . ' ' . $responseToken->body->access_token;
        dump($responseToken);
        dump($token);
        return $token;
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
            // $da = \DateTime::createFromFormat("Y-m-d",$searchParametersObj['itemOffered_arrivalTime']);
            $da = \DateTime::createFromFormat("D M d Y",$searchParametersObj['itemOffered_arrivalTime']);
            // $inboundPartialDate =  $da->format('Y-m-d');
            $searchQuery['returndate'] =  $da->format('Y-m-d');
        } else {
            $searchQuery['returndate'] = '2017-07-08';
        }
        if (array_key_exists('itemOffered_departureTime',$searchParametersObj)) {
            // $dd = \DateTime::createFromFormat("Y-m-d",$searchParametersObj['itemOffered_departureTime']);
            $dd = \DateTime::createFromFormat("D M d Y",$searchParametersObj['itemOffered_departureTime']);
            // $outboundPartialDate =  $dd->format('Y-m-d');
            $searchQuery['departuredate'] =  $dd->format('Y-m-d');
        } else {
            $searchQuery['departuredate'] = '2017-07-07';
        }

        // $token = $this->getSabreToken();
        $token = 'bearer T1RLAQKy/KrwJyisnf4MnjOu3WmPT+7rnhD+SmxhaDCJzV4Z4YbtiIQhAADAAltSEMwd6Lq0AzX9dQL6YfC1a6eVgrCkx0wXE3yxk5D8WtAv6OVy3ipinX2p5andEz/YJ7yy1f5G7X9HlDk/s8WIc3yS7XAsLYJzCe+mJ3WSQkmjfPYWWB7A/DnmLH/7iZ/YgpfaMLTW41ukdXpbCrQ2HRxsZbVFwg30s14Hx/ZIWJ7JUentyaZ6O9p1KI8HQHZysDDQR0ArVjvDH5AsdBcpmPg+G13xjWKJUZ4k32GTwjGaQGM4gKE84IcfEf9q';

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

        dump($query);
        $url = 'https://api.test.sabre.com/v1/shop/flights';
        $response = Unirest\Request::get($url,$headers,$query);

        dump($response);

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
            $offered[$key]->setPrice($value->AirItineraryPricingInfo->ItinTotalFare->TotalFare->Amount);
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
            return [$offered];
    }
}