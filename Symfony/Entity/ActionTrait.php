<?php
namespace Magice\Symfony\Entity;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ActionTrait
{
    public function apply($data)
    {
        $me = new \ReflectionClass($this);

        if (is_object($data)) {
            $cls = new \ReflectionClass($data);
            foreach ($cls->getProperties() as $property) {
                $name = $property->getName();

                if ($me->hasProperty($name)) {

                    $setter = 'set' . $name;
                    $getter = 'get' . $name;

                    if ($property->isPublic()) {
                        $this->$setter($data->$name);
                    } elseif ($cls->hasMethod($getter)) {
                        $this->$setter($data->$getter());
                    } else {
                        // cannot get value
                        continue;
                    }
                }
            }
        }

        if (is_array($data)) {
            foreach ($data as $name => $value) {
                if ($me->hasProperty($name)) {
                    $setter = 'set' . $name;
                    $this->$setter($value);
                }
            }
        }

        return $this;
    }

    /**
     * @param EntityManager                   $em
     * @param array|object|ValidatorInterface $data      (ValidatorInterface use as validator)
     * @param null|ValidatorInterface|array   $validator (array use validatoionGroups)
     * @param null|array                      $validatorGroups
     *
     * @return $this
     * @throws EntityValidatorException
     */
    public function save(EntityManager $em, $data = null, $validator = null, $validatorGroups = null)
    {
        // shift arg
        if ($data instanceof ValidatorInterface) {
            $validatorGroups = $validator;
            $validator       = $data;
            $data            = null;
        }

        if ($data) {
            $this->apply($data);
        }

        if ($validator) {
            $this->validate($validator, $validatorGroups);
        }

        $em->persist($this);
        $em->flush();

        return $this;
    }

    public function validate(ValidatorInterface $validator, array $validatorGroups = null)
    {
        $errors = $validator->validate($this, null, $validatorGroups);

        if ($errors->count()) {
            # if you want errors, you should use try-catch block and get error with: $e->getErrors()
            $e = new EntityValidatorException(sprintf("Validation of %s is not pass.", get_class($this)));
            $e->setErrors($errors);

            throw $e;
        }

        return $this;
    }

    public function remove(EntityManager $em)
    {
        $em->remove($this);
        $em->flush();
    }
}