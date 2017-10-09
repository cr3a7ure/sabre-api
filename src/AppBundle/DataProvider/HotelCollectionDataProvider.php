<?php

// src/AppBundle/DataProvider/HotelCollectionDataProvider.php

namespace AppBundle\DataProvider;

use AppBundle\Entity\Hotel;
use AppBundle\Entity\GeoCoordinates;
use AppBundle\Entity\Offer;
use AppBundle\Entity\PostalAddress;
use AppBundle\Entity\Rating;
use AppBundle\Entity\PriceSpecification;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Unirest;

final class HotelCollectionDataProvider implements CollectionDataProviderInterface
{
  protected $requestStack;
  protected $managerRegistry;
  // protected $objectManager;

  public function __construct(RequestStack $requestStack,ManagerRegistry $managerRegistry)
    {
        $this->requestStack = $requestStack;
        $this->managerRegistry = $managerRegistry;
        // $this->objectManager = $objectManager;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        if (Hotel::class !== $resourceClass) {
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

        $query = array();
        $query['GeoSearchRQ'] = array();
        $query['GeoSearchRQ']['version'] = "1";
        $query['GeoSearchRQ']['GeoRef'] = array();
        $query['GeoSearchRQ']['GeoRef']['GeoCode'] = array();

        if (array_key_exists('longitude',$searchQuery)) {
            $query['GeoSearchRQ']['GeoRef']['GeoCode']['Longitude'] = floatval($searchQuery['longitude']);
        } else {
            $query['GeoSearchRQ']['GeoRef']['GeoCode']['Longitude'] = 23.951255;
        }
        if (array_key_exists('latitude',$searchQuery)) {
            $query['GeoSearchRQ']['GeoRef']['GeoCode']['Latitude'] = floatval($searchQuery['latitude']);
        } else {
            $query['GeoSearchRQ']['GeoRef']['GeoCode']['Latitude'] = 37.936357;//23.951255;
        }

        $query['GeoSearchRQ']['GeoRef']['Category'] = 'HOTEL';
        $query['GeoSearchRQ']['GeoRef']['Radius'] = 50;
        $query['GeoSearchRQ']['GeoRef']['UOM'] = 'KM';
        // dump($query);


        $token = 'bearer T1RLAQIdFbN195gm3G3AUaUjddM9JW6ulhBqhCWrMS7sFdXh3YrmNzD0AADABpc1DvFHLF6EDyvrDJxxM4ewAT6MCVD6ArSD6xRP/6VC5FewXSi2ZmGd/cRtx/rAL7nMuCH/0HwUZkCQcySIvRs0EZqgTal1aPcQh8WUL0iYZkU/Rrbf0osC5APhcRLOt2kSc25g3iqlppokSrQPG6FDA3VJ9uRAhVnnqETHYlWaH04sREsTkOj3UPRXQ9hZ1m1SWsJ32UnR9WhNaJlv6MBDRdXZXpQ59au5NiH1ecyDuPkDbj0SbcDRjD1xBnK2';

        $headers = array('Authorization' => $token, 'Content-Type' => 'application/json' );
        // $headers = array('X-Originating-Ip' => '94.66.220.69');

        $url = 'https://api.test.sabre.com/v1.0.0/lists/utilities/geosearch/locations?mode=geosearch';
        $body = Unirest\Request\Body::json($query);

        $response = Unirest\Request::post($url,$headers,$body);
        dump($response);
        $results = $response->body->GeoSearchRS->GeoSearchResults;
        if (!(array_key_exists('GeoSearchResult',$results))) {
            // $responseOut = new Response();
            // $responseOut->setStatusCode(Response::HTTP_NO_CONTENT);

            return [];
        } else {

            $resultsArray = $response->body->GeoSearchRS->GeoSearchResults->GeoSearchResult;
            $hotelsArray = array();
            $postalArray = array();
            $geoArray = array();
            foreach ($resultsArray as $key => $value) {
                $id = $value->Id;
                $hotelsArray[$id] = new Hotel();
                $postalArray[$key] = new PostalAddress();
                $geoArray[$key] = new GeoCoordinates();
                $postalArray[$key]->setAddressCountry($value->Country);
                $postalArray[$key]->setId($key);
                $postalArray[$key]->setAddressLocality($value->City);
                // $postalArray[$key]->setAddressRegion($value->);
                if (property_exists($value,'Zip')) {
                    if (preg_match('/\\d/', $value->Zip)) {
                        $postalArray[$key]->setPostalCode($value->Zip);
                    }
                } else {
                    $postalArray[$key]->setPostalCode('00000');
                }
                // $postalArray[$key]->setStreetAddress($value->Street);
                $geoArray[$key]->setId($key);
                $geoArray[$key]->setLatitude($value->Latitude);
                $geoArray[$key]->setLongitude($value->Longitude);
                $distance = $value->Distance;
                $hotelsArray[$id]->setName($value->Name);
                $hotelsArray[$id]->setId($value->Id);
            }
            // dump($hotelsArray);
            $url = 'https://api.test.sabre.com/v1.0.0/shop/hotels/description?mode=description';
            $query = array();
            $query['GetHotelDescriptiveInfoRQ'] = array();
            $query['GetHotelDescriptiveInfoRQ']['HotelRefs'] = array();
            $query['GetHotelDescriptiveInfoRQ']['HotelRefs']['HotelRef'] = array();
            // $query['GetHotelDescriptiveInfoRQ']['HotelRefs']['HotelRef']['HotelCode'] = '1100';
            //
            $query['GetHotelDescriptiveInfoRQ']['DescriptiveInfoRef'] = array();
            $query['GetHotelDescriptiveInfoRQ']['DescriptiveInfoRef']['PropertyInfo'] = false;
            $query['GetHotelDescriptiveInfoRQ']['DescriptiveInfoRef']['LocationInfo'] = true;
            $query['GetHotelDescriptiveInfoRQ']['DescriptiveInfoRef']['Amenities'] = false;
            $query['GetHotelDescriptiveInfoRQ']['DescriptiveInfoRef']['Airports'] = true;
            $query['GetHotelDescriptiveInfoRQ']['DescriptiveInfoRef']['AcceptedCreditCards'] = false;
            $query['GetHotelDescriptiveInfoRQ']['DescriptiveInfoRef']['Descriptions'] = array();
            $query['GetHotelDescriptiveInfoRQ']['DescriptiveInfoRef']['Descriptions']['Description'] = array();
            array_push($query['GetHotelDescriptiveInfoRQ']['DescriptiveInfoRef']['Descriptions']['Description'],array('Type'=> 'ShortDescription'));
            // $query['GetHotelDescriptiveInfoRQ']['DescriptiveInfoRef']['Descriptions']['Description']['Type'] = 'ShortDescription';

            foreach ($hotelsArray as $key => $value) {
                $temp = (object) array('HotelCode'=> $value->getId());
                array_push( $query['GetHotelDescriptiveInfoRQ']['HotelRefs']['HotelRef'],$temp);
                // $query['GetHotelDescriptiveInfoRQ']['HotelRefs']['HotelRef'] += array('HotelCode'=> $value->getId());
            }

            // dump($query);
            $body = Unirest\Request\Body::json($query);
            // dump($body);
            $response = Unirest\Request::post($url,$headers,$body);
            dump($response);
            $hotels = array();
            $geolocs = array();
            $addresses = array();
            $prices = array();
            $ratings = array();
            $data = $response->body->GetHotelDescriptiveInfoRS->HotelDescriptiveInfos->HotelDescriptiveInfo; //array

            $counter = 0;
            foreach ($data as $key => $value) {
                $v = $value->HotelInfo;
                $l = $value->LocationInfo;
                if (array_key_exists('Status',$v)) {
                    if ($v->Status=='Inactive') {
                        continue;
                    }
                } else {
                    continue;
                }
                if (property_exists($value,'Descriptions')) {
                    $desc = ( property_exists($value->Descriptions,'Description') ? $value->Descriptions->Description[0]->Text->content : '' );
                    // $desc = $value->Descriptions->Description[0]->Text->content;
                } else {
                    $desc = '';
                }
                $id = $v->HotelCode;
                $hotels[$counter] = new Hotel();
                $hotels[$counter] = $hotelsArray[$id];
                $hotels[$counter]->setName($v->HotelName);
                $hotels[$counter]->setDescription($desc);
                $hotels[$counter]->setTelephone($l->Contact->Phone);
                $hotels[$counter]->setFaxNumber($l->Contact->Fax);
                $adresses[$counter] = new PostalAddress();
                $adresses[$counter]->setId($key);
                $adresses[$counter]->setAddressCountry($l->Address->CountryName->Code);
                $adresses[$counter]->setAddressLocality($l->Address->CityName);
                $adresses[$counter]->setAddressRegion($l->Address->StateProv->content);
                // $adresses[$counter]->setPostalCode($l->Address->PostalCode);
                $adresses[$counter]->setPostalCode( property_exists($l->Address,'PostalCode') ? $l->Address->PostalCode : '' );
                $adresses[$counter]->setStreetAddress($l->Address->AddressLine1);

                $geolocs[$counter] = new GeoCoordinates();
                $geolocs[$counter]->setId(($key));
                $geolocs[$counter]->setLatitude($l->Latitude);
                $geolocs[$counter]->setLongitude($l->Longitude);
                $hotels[$counter]->setAddress($adresses[$counter]);
                // $hotels[$key]->setMakesOffer(null);
                // $hotels[$key]->setPriceRange($prices[$key]);
                // $hotels[$key]->setAward("Check rating");
                $hotels[$counter]->setGeo($geolocs[$counter]);
                // $hotels[$key]->setGeo(null);
                $counter++;
            }
            // dump($hotels);
            // $hotels = [];
            return $hotels;
        }
    }
}