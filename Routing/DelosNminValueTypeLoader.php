<?php

namespace Delos\Nmin\BaseBundle\Routing;

use App\Delos\Nmin\BaseBundle\Annotation\MapToUrlParameter;
use App\Delos\Nmin\BaseBundle\DelosNminValueInterface\DelosNminValueInterface;
use App\Delos\Nmin\BaseBundle\DelosNminControllerInterface\DelosNminControllerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class DelosNminValueTypeLoader extends Loader {
    private $isLoaded = false;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function load($resource, $type = null) {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "DelosNminValueType" loader twice');
        }

        $routes = new RouteCollection();

        $finder = new Finder();

        $files = $finder->files()
            ->in(__DIR__.'/../..')
            ->path('/Controller\/\w+Controller.php$/');

        $controllers = array();
        foreach ($files as $file) {
            $namespace = 'App\\Delos\\Nmin\\'.
                str_replace('/','\\',
                    array_reverse(
                        explode('../',
                            pathinfo($file->getPathname(), PATHINFO_DIRNAME)
                        )
                    )[0]
                );
            $classname = $namespace.'\\'.pathinfo($file->getFilename(),PATHINFO_FILENAME);
            if (in_array(DelosNminControllerInterface::class,class_implements($classname))) {
                $controllers[] = $classname;
            }
            usort($controllers,function ($a,$b) {
                // echo "\n" .$a . '<>' . $b . "\n".$a::getPriority() .' -- '.$b::getPriority() .' : '.   (($a::getPriority() < $b::getPriority()) ? -1 : (($a::getPriority() > $b::getPriority()) ? 1 : 0));
                return (($a::getPriority() < $b::getPriority()) ? 1 : (($a::getPriority() > $b::getPriority()) ? -1 : 0));
            });
        }

        $md = $this->em->getMetadataFactory()->getAllMetadata();
        $ar = new AnnotationReader();
        foreach ($md as $classMetadata) {
            foreach ($controllers as $controller) {
                $class = $classMetadata->getName();
                if (in_array(DelosNminValueInterface::class,class_implements($class)) && $class::isAccessable()) {
                    foreach ($controller::getMethods() as $method => $addParams) {
                        $params = array();
                        if (strpos($method,'_') !== 0) {
                            $params[] = $method;
                        }
                        $name = trim($method,'_');
                        $method = $name;

                        $method = '_'.$method;

                        $defaults['_controller'] = $controller.'::'.$method;
                        $defaults['_entity_class'] = $class;
                        $requirements = $controller::getRequirements();
                        $requirements = array_filter($requirements,function($key) use ($name) {
                            $elements = explode('/',$key);
                            if (count($elements) > 1) {
                                array_pop($elements);
                                return in_array($name,$elements);
                            }
                            return true;
                        },ARRAY_FILTER_USE_KEY);
                        foreach (array_keys($requirements) as $key) {
                            if (preg_match('/\/([^\/]*)$/',$key,$matches)) {
                                $requirements[$matches[1]] = $requirements[$key];
                                unset($requirements[$key]);
                            }
                        }
                        $methods = array();
                        if (key_exists('_method', $requirements)) {
                            $methods[] = $requirements['_method'];
                            unset($requirements['_method']);
                        }
                        $originalRequirements = $requirements;
                        if (is_null($addParams)) {
                            $fields = $classMetadata->getFieldNames();

                            foreach ($fields as $field) {

                                try {
                                    $reflection = new \ReflectionProperty($class,$field);
                                    $param = $ar->getPropertyAnnotation($reflection,MapToUrlParameter::class);
                                    if ($param) {
                                        if ($param->showInUrl) {
                                            $params[] = '{'.$field.'}';
                                        }
                                        if ($param->requirement) {
                                            $requirements[$field] = $param->requirement;
                                        }
                                        if ($param->default) {
                                            $defaults[$field] = $param->default;
                                        }
                                    }

                                } catch (\ReflectionException $exception) {

                                }
                            }
                        } elseif (is_string($addParams)) {
                            $addParams = array($addParams=>null);
                        }

                        foreach (array_keys($originalRequirements) as $field) {
                            if (strpos($field,'_') !== 0 && (!is_array($addParams) || !in_array($field,$addParams))) {
                                array_unshift($params,'{'.$field.'}');
                            }
                        }

                        if (is_array($addParams)) {
                            foreach ($addParams as $f => $r) {
                                if (is_numeric($f)) {
                                    $f = $r;
                                    $r = null;
                                }
                                if ($f === '_method') {
                                    $methods[] = $r;
                                } else {
                                    if (strpos($f,'_') !== 0) {
                                        $params[] = '{'.$f.'}';
                                    }
                                    if (is_string($r)) {
                                        $r = array($r,null);
                                    }
                                    list($r,$d) = $r;
                                    if (!is_null($r)) {
                                        $requirements[$f] = $r;
                                    }
                                    if (!is_null($d)) {
                                        $defaults[$f] = $d;
                                    }

                                }

                            }

                        }
                        $params = implode('/',$params);
                        if (!empty($params)) {
                            $params = '/'.$params;
                        }
                        foreach ($requirements as $field => $requirement) {
                            if ($requirement === '_classtype_') {
                                $requirements[$field] = $class::getClasstype();
                            }
                        }
                        $routes->add(
                            trim($controller::getUrlPrefix().'_'.str_replace('/','_',(key_exists('type',$requirements)) ? $class::getClassType().'_' : '').$name,'_'),
                            new Route(
                                $controller::getUrlPrefix('/',false).$params,
                                $defaults,
                                $requirements,
                                array('utf8' => true),
                                null,
                                null,
                                $methods,
                                key_exists('_format',$requirements) ? "'application/".$requirements['_format']."' in request.getAcceptableContentTypes() or 'text/".$requirements['_format']."' in request.getAcceptableContentTypes()" : null
                            )
                        );
                    }
                }

            }
        }
        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        // echo $type; die;
        return 'delos_nmin_dispatch' === $type;
    }
}
?>