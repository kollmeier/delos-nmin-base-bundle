<?php
/**
 * Created by PhpStorm.
 * User: carstenkollmeier
 * Date: 24.10.18
 * Time: 11:57
 */

namespace App\Delos\Nmin\BaseBundle\Service;


use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GetSetData
{
    public function getFromData($keys, &$data) {
        if (is_string($keys)) {
            $keys = array($keys);
        }
        if (is_array($data)) {
            $d = $data;
            $success = false;
            foreach ($keys as $key) {
                if (!is_array($d)) {
                    $success = false;
                    break;
                }
                if (key_exists($key,$d)) {
                    $d = &$d[$key];
                    $success = true;
                }
            }
            if ($success) {
                return $d;
            } else {
                return null;
            }
        }
        $key = array_pop($keys);
        $method = 'get'.ucfirst($key);
        if (method_exists($data,$method)) {
            return $data->$method();
        }
        return null;
    }

    public function setToData($keys, $value, &$data) {
        if (is_string($keys)) {
            $keys = array($keys);
        }
        if (is_array($data)) {
            $d = $data;
            $i = &$d;
            $lk = array_pop($keys);
            foreach ($keys as $key) {
                if (is_array($i)) {
                    if (key_exists($key,$i) && is_array($i[$key])) {
                        $i = &$i[$key];
                    }
                }
            }
            $i[$lk] = $value;
            $data = $d;

//            $d = $value;
//            $lk = null;
//            while (!empty($keys)) {
//                $lk = array_pop($keys);
//                $d = array($lk => $d);
//            }
//            $data = array_merge($data,$d);
        } else {
            $key = array_pop($keys);
            $method = 'set'.ucfirst($key);
            if (method_exists($data,$method)) {
                $data->$method($value);
            }
        }
        return $data;
    }

}