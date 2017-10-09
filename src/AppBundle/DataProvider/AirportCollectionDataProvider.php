<?php

// src/AppBundle/DataProvider/AirportCollectionDataProvider.php

namespace AppBundle\DataProvider;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Airport;
use AppBundle\Entity\Flight;
use AppBundle\Entity\PostalAddress;
use AppBundle\Entity\GeoCoordinates;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Unirest;

final class AirportCollectionDataProvider implements CollectionDataProviderInterface
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

    protected function getSabreToken() {
        $clientId = 'VjE6Mmh4dXhjbTZoejRhZXg1bjpERVZDRU5URVI6RVhU';
        $encodedId = base64_encode($clientId);
        $clientSecret = 'TmhBVGxoODE=';
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
        if (Airport::class !== $resourceClass) {
            throw new ResourceClassNotSupportedException();
        }
        // Retrieve search parameters
        $request = $this->requestStack->getCurrentRequest();
        $searchParametersObj = $request->query->all();
        $searchParametersKeys = array_keys($searchParametersObj);
        // dump($searchParametersObj);
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

        if (array_key_exists('iataCode',$searchQuery)) {
             $query['query'] = $searchQuery['iataCode'];
             $query['limit'] = '1';
        } elseif (array_key_exists('addressLocality',$searchQuery)) {
             $query['query'] = $searchQuery['addressLocality'];
             $query['limit'] = '5';
        } elseif (array_key_exists('addressCountry',$searchQuery)) {
             $query['query'] = $searchQuery['addressCountry'];
             $query['limit'] = '10';
        } else {
             $query['query'] = 'ATH';
        }

        //AUTOCOMPLETE
        $token = 'bearer T1RLAQIdFbN195gm3G3AUaUjddM9JW6ulhBqhCWrMS7sFdXh3YrmNzD0AADABpc1DvFHLF6EDyvrDJxxM4ewAT6MCVD6ArSD6xRP/6VC5FewXSi2ZmGd/cRtx/rAL7nMuCH/0HwUZkCQcySIvRs0EZqgTal1aPcQh8WUL0iYZkU/Rrbf0osC5APhcRLOt2kSc25g3iqlppokSrQPG6FDA3VJ9uRAhVnnqETHYlWaH04sREsTkOj3UPRXQ9hZ1m1SWsJ32UnR9WhNaJlv6MBDRdXZXpQ59au5NiH1ecyDuPkDbj0SbcDRjD1xBnK2';
        $query['category'] = 'AIR';//other categories AIR, CITY, RAIL

        $url = 'https://api.test.sabre.com/v1/lists/utilities/geoservices/autocomplete';
        $headers = array('Authorization' => $token, 'Content-Type' => 'application/json' );
        $response = Unirest\Request::get($url,$headers,$query);
        // dump($response);
        $elem = 'category:'.$query['category']; //'category:AIR';
        $result = $response->body->Response;
        if (!(array_key_exists('grouped',$result))) {
            return [];
        } else {
            $autocompleteArray = $response->body->Response->grouped->$elem->doclist->docs;
            // $autocompleteArray = $response->Response->grouped['category:AIR']->doclist->docs;
            $airports = array();
            $postal = array();
            $geo = array();

            foreach ($autocompleteArray as $key => $value) {
                $airports[$key] = new Airport();
                $airports[$key]->setId($key);
                $postal[$key] = new PostalAddress();
                $airports[$key]->setIataCode($value->iataCityCode);
                $geo[$key] = new GeoCoordinates();
                $postal[$key]->setAddressCountry($value->country);
                $postal[$key]->setId($key);
                $postal[$key]->setAddressLocality($value->city);
                // $postal[$key]->setAddressRegion($value->);
                // $postal[$key]->setPostalCode($value->Zip);
                // $postal[$key]->setStreetAddress($value->Street);
                $geo[$key]->setId($key);
                $geo[$key]->setLatitude($value->latitude);
                $geo[$key]->setLongitude($value->longitude);
                $airports[$key]->setAddress($postal[$key]);
                $airports[$key]->setGeo($geo[$key]);
            }

            return $airports;
        }
    }
}