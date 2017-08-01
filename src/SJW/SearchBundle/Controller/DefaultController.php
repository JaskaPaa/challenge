<?php

namespace SJW\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;



class DefaultController extends Controller
{

    /**
     * @Route("/")
     *
     * @Template()
     */

    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/api/search")
     *
     * @Template()
     */
    public function searchAction(Request $request) {

        
        $searchString = trim($request->query->get('q'));
        $input = explode('|', $request->query->get('q'));
        $searchString = trim($input[0]);
        
        if (strlen($searchString) == 0 || !ctype_digit($input[1])) {
            return new JsonResponse(array());
        }

        $n = intval($input[1]);

        
        $numbers = $this->getPostalArray();

        $field =  ctype_digit($searchString) ? 0 : 1;

        $key = array_search($searchString, array_column($numbers, $field));

        if ($key === false)
           return new JsonResponse(array()); 

        $respo = array_slice($numbers, $key - $n, 2*$n + 1);

        // Output content.
        return new JsonResponse($respo);
    }

    /**
     * @Route("/api/complete")
     */

    public function autoComplete(Request $request) {

        $searchString = trim($request->query->get('term'));

        $numbers = $this->getPostalArray();

        
        // if search is a number use $field 0 else 1
        $field =  ctype_digit($searchString) ? 0 : 1;

        // filter search mathces from first char  
        $res = array_filter($numbers, function($k) use ($numbers, $searchString, $field) {
            $pos = strpos(strtolower($numbers[$k][$field]), strtolower($searchString));
            return $pos !== false and $pos == 0;
        }, ARRAY_FILTER_USE_KEY);

        //error_log( array_keys($res)[0] . " ---\n", 3, "/home/jaska/koodi/phplog");

        $keys = array_keys($res);

        $respo = array();

        foreach($keys as $key) {
            $respo[] = $numbers[$key][$field];
        }

        sort($respo);

        // Output the first 25
        return new JsonResponse(array_slice($respo, 0, 25));

    }


    private function getPostalArray() {
        
        // Read resource file.

        $kernel = $this->get('kernel');

        $filePath = $kernel->locateResource('@SJWSearchBundle/Resources/data/numbers.txt');

        // Split lines and comma delimited values.
        $lines = explode("\n", file_get_contents($filePath, true));

        $numbers = array();

        foreach($lines as $line) {
            $numbers[] = explode(';', $line);
        }
        
        // sort by population
        usort($numbers, function ($a, $b) {
            return $a[2] - $b[2];
        });

        return $numbers;
    }

    
}

// future need ??
function build_sorter($key) {
    
    return function ($a, $b) use ($key) {
        return strnatcmp( strtolower($a[$key]), strtolower($b[$key]) );
    };
}
