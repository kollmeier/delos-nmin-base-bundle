<?php
/**
 * Created by PhpStorm.
 * User: carstenkollmeier
 * Date: 18.10.18
 * Time: 10:46
 */

namespace Delos\Nmin\BaseBundle\Service;


use App\Delos\Nmin\Data\BaseBundle\Entity\DelosNminValue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\TranslatorInterface;

class EntityTypes
{
    private $em ;
    private $entities = array();
    private $discriminators;
    private $types;
    private $class;
    private $classMetadataFactory;
    private $translator;

    public function __construct(EntityManagerInterface $em, ClassMetadataFactoryInterface $classMetadataFactory, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->classMetadataFactory = $classMetadataFactory;
        $this->class = DelosNminValue::class;
        $this->translator = $translator;
    }

    public function &getDiscriminators()
    {
        if (!$this->discriminators)
        {
            $this->discriminators = $this->em->getClassMetadata($this->class)->discriminatorMap;
        }
        return $this->discriminators;
    }

    public function &getTypes()
    {
        if (!$this->types && !is_array($this->types))
        {
            $this->types = array();

            $discriminators = $this->getDiscriminators();
            if (is_array($discriminators) && !empty($discriminators)) {
                foreach ($discriminators as $discriminator => $classname) {
                    if ($classname::isAccessable()) {
                        $this->types[$this->translator->trans($discriminator)] = $discriminator;
                    }
                }
            }
        }

        return $this->types;
    }

    public function getEntity($type, ?int $id=null): ?DelosNminValue
    {
        if (!$id && isset($this->entities[$type])) {
            return $this->entities[$type];
        }
        $t = $this->getDiscriminators()[$type];
        if (!$id) {
            $this->entities[$type] = new $t();
            return  $this->entities[$type];
        }
        $repository = $this->em->getRepository($t);
        return $repository->find($id);
    }

    public function getFieldClass($entity,$field) {
        return $this->em->getClassMetadata(get_class($entity))->getTypeOfField($field);
    }

    public function getFields($t,?string $group = 'editable')
    {
        $fields = array();

        if (in_array($t, $this->getTypes())) {
            $entity = $this->getEntity($t);

            $serializer = new Serializer(array(new ObjectNormalizer($this->classMetadataFactory)));
            $fields = array_keys($serializer->normalize($entity,null,array('groups' => array($group))));
//            $fields = $this->em->getClassMetadata(get_class($entity))-> getFieldNames();
        }
//        if (!empty($fields)) {
//            $fields = array_diff($fields,['id', 'type','url','valid_from','created_at','updated_at','original_values']);
//        }
        return $fields;
    }


}