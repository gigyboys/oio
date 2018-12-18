<?php

namespace App\DataFixtures;

use App\Entity\DocumentAuthorization;
use App\Entity\Option;
use App\Entity\Parameter;
use App\Entity\School;
use App\Entity\Type;
use App\Entity\User;
use App\Repository\OptionRepository;
use App\Repository\TypeRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $em)
    {
        $this->loadUser($em);
        $this->loadType($em);
        $this->loadOption($em);
        $this->loadDocumentAuthorization($em);
        $this->loadParameter($em);
    }

    public function loadUser(ObjectManager $em)
    {
        //user1
        $user = new User();
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setName('Duxlem ipsum');
        $user->setEmail('user1@boot.com');
        $user->setUsername('user1');
        $user->setPassword($this->passwordEncoder->encodePassword($user,'user1'));
        $user->setEnabled(true);
        $user->setDate(new \DateTime());
        $user->setToken(md5(time()));
        $em->persist($user);

        //user2
        $user = new User();
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setName('Duxlem ipsum 2');
        $user->setEmail('user2@boot.com');
        $user->setUsername('user2');
        $user->setPassword($this->passwordEncoder->encodePassword($user,'user2'));
        $user->setEnabled(true);
        $user->setDate(new \DateTime());
        $user->setToken(md5(time()));
        $em->persist($user);
        $em->flush();
    }

    public function loadType(ObjectManager $em)
    {
        $typeArrays = array(
            array(
                'name' => "public",
                'slug' => "public",
                'plural_name' => "publics",
            ),
            array(
                'name' => "privé",
                'slug' => "private",
                'plural_name' => "privés",
            ),
        );

        foreach ($typeArrays as $typeArray)
        {
            $entity = new Type();
            $entity->setName($typeArray['name']);
            $entity->setSlug($typeArray['slug']);
            $entity->setPluralName($typeArray['plural_name']);
            $em->persist($entity);
        }
        $em->flush();
    }

    public function loadDocumentAuthorization(ObjectManager $em)
    {
        $authorizationArrays = array(
            array(
                'name' => "anonymous",
                'description' => "Le document est accessible par tout le monde",
            ),
            array(
                'name' => "connected",
                'description' => "Le document est accessible par les utilisateurs connectés",
            ),
            array(
                'name' => "subscribed",
                'description' => "Le document est accessible par les utilisateurs connectés et abonnés à l'établissement",
            ),
        );

        foreach ($authorizationArrays as $authorizationArray)
        {
            $entity = new DocumentAuthorization();
            $entity->setName($authorizationArray['name']);
            $entity->setDescription($authorizationArray['description']);
            $em->persist($entity);
        }
        $em->flush();
    }

    public function loadOption(ObjectManager $em)
    {
        $optionArrays = array(
            array(
                'name' => "filière",
                'plural_name' => "filières",
            ),
            array(
                'name' => "formation",
                'plural_name' => "formations",
            ),
            array(
                'name' => "mention",
                'plural_name' => "mentions",
            ),
            array(
                'name' => "branche",
                'plural_name' => "branches",
            ),
            array(
                'name' => "département",
                'plural_name' => "départements",
            ),
            array(
                'name' => "cursus",
                'plural_name' => "cursus",
            ),
        );

        foreach ($optionArrays as $optionArray)
        {
            $entity = new Option();
            $entity->setName($optionArray['name']);
            $entity->setPluralName($optionArray['plural_name']);
            $em->persist($entity);
        }
        $em->flush();
    }

    public function loadParameter(ObjectManager $em)
    {
        $parameterArrays = array(
            array(
                'parameter' => "populate",
                'value' => "1",
            ),
            array(
                'parameter' => "schools_by_page",
                'value' => "12",
            ),
            array(
                'parameter' => "categories_index",
                'value' => "12",
            ),
            array(
                'parameter' => "posts_by_page",
                'value' => "12",
            ),
        );

        foreach ($parameterArrays as $parameterArray)
        {
            $entity = new Parameter();
            $entity->setParameter($parameterArray['parameter']);
            $entity->setValue($parameterArray['value']);
            $em->persist($entity);
        }
        $em->flush();
    }
}
