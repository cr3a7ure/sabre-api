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
        $token = 'bearer T1RLAQIf5uRGe+gkncyM4P5m+Cq303wYYhA7+S1CbcMbVqOU3ypdKztwAADATKumPv1n2M/q/8jq3+lk6wufE3Nbd71MGhFqZXEOv5RCdIYfHHiSM8qOOqoCyhlGe5S3fgB85m0e9zvzwEprDuCNB4Jf+ZztY/ls8Mz6RIwqYYotJOJ/VePI2BNznzqeTDogU/twV6glx4D69UqmTRjRkLeajtYwimqfg8LAuIygK77SyNqTz7Lmo0SNZ2xqZj/PQRrxeWmG76xWoEoP3iDDmqYs15Gdgv4HmxJMQKI/wElIgY7STkHwWur2DkM7';
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

            return [$airports];
        }
    }
}