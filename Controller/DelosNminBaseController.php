<?php

namespace App\Delos\Nmin\BaseBundle\Controller;

use App\Delos\Nmin\BaseBundle\DelosNminValueInterface\DelosNminValueInterface;
use App\Delos\Nmin\Data\BaseBundle\Entity\DelosNminValue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Delos\Nmin\BaseBundle\DelosNminControllerInterface\DelosNminControllerInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class DelosNminBaseController extends AbstractController implements DelosNminControllerInterface
{
    protected static $urlPrefix = 'data';

    protected static $allowedMethods = array(
        '_get' => null,
//        '_list' => array(),
//        '_show' => array('id'=>'\\d+', '_method' => 'GET'),
//        '_new' => array('_method' => 'POST'),
//        '_edit' => array('id'=>'\\d+', '_method' => 'PUT'),
//        '_delete' => array('id'=>'\\d+', '_method' => 'DELETE'),
    );

    protected static $requirements = array(
//              '_format' => 'html',
        'get/type' => '_classtype_',
        'get/_method' => 'GET',
    );


    public function notImplemented(?string $name, ?array $arguments)

    {
        return $this->json(array('_meta' => array('info' => 'Not implemented yet','controller' => static::class, 'name'=>$name), 'arguments' => $arguments),Response::HTTP_NOT_IMPLEMENTED);
    }

    public function _get(Request $request) {
        return $this->notImplemented('get',array_merge(array('_acceptable' => $request->getAcceptableContentTypes()),$request->attributes->all()));
    }

    public function _list(Request $request) {
        return $this->notImplemented('list',$request->attributes->all());
    }

    public function _show(DelosNminValue $delosNminValue) {
        return $this->notImplemented('show',$request->attributes->all());
    }

    public function _edit(Request $request) {
        return $this->notImplemented('edit',$request->attributes->all());
    }

    public function _new(Request $request) {
        return $this->notImplemented('new',$request->attributes->all());
    }

    public function _delete(Request $request) {
        return $this->notImplemented('delete',$request->attributes->all());
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public static function getMethods(): array
    {
        return static::$allowedMethods;
    }

    public static function getRequirements(): array
    {
        return static::$requirements;
    }

    public static function getCatchAll(): ?string
    {
        return 'notImplemented';
    }

    public static function getUrlPrefix(?string $delimiter='_',?bool $recurse=true): string
    {
        $parent = get_parent_class(static::class);
        if ($recurse && key_exists(DelosNminControllerInterface::class, class_implements($parent))) {
            return $parent::getUrlPrefix($delimiter).$delimiter.static::$urlPrefix;
        }
        return $delimiter.self::$urlPrefix;
    }

//    private function getFunctionSegments(string $name): array
//    {
//        $convert = new CamelCaseToSnakeCaseNameConverter();
//        $name = $convert->normalize($name);
//        $segments = explode('_',$name);
//        $method = array_shift($segments);
//        $entityName = $convert->denormalize(implode('_',$segments));
//
//        return array($method,$entityName);
//    }
//
//    public function __call($name, $arguments)
//    {
//        list($method,$entityName) = $this->getFunctionSegments($name);
//
//        var_dump($method);
//
//        if ((in_array($method,static::$allowedMethods) || in_array('_'.$method,static::$allowedMethods)) && is_callable(array($this,$method))) {
//            $method = '_'.$method;
//            return $this->$method($entityName,$arguments);
//        }
//        return $this->notImplemented($method,$arguments);
//    }
}
