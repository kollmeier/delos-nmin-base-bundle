<?php
/**
 * Created by PhpStorm.
 * User: carstenkollmeier
 * Date: 20.11.18
 * Time: 06:48
 */

namespace Delos\Nmin\BaseBundle\Serializer\Normalizer;


use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DelosObjectNormalizer extends ObjectNormalizer
{
    public function __construct (ClassMetadataFactoryInterface $classMetadataFactory=null,NameConverterInterface $nameConverter=null, PropertyAccessorInterface $propertyAccessor=null, PropertyTypeExtractorInterface $typeExtractor=null,ClassDiscriminatorResolverInterface $discriminatorResolver=null )
    {
        parent::__construct($classMetadataFactory,$nameConverter,$propertyAccessor,$typeExtractor, $discriminatorResolver);

//        $this->setIgnoredAttributes(array('importData','importedValues'));

        $this->setMaxDepthHandler(function ($object) {
            if ($object instanceof Collection) {
                $map = $object->map(function ($object) {
                    return get_class($object);
                });
                return $map;
            }
            return $object->getId();
        });

        $this->setCircularReferenceLimit(1);

        $this->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
    }


}