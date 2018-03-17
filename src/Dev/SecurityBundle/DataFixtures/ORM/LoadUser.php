<?php

namespace Dev\SecurityBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Dev\SecurityBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUser extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    protected $roles = [
        'ROLE_RECRUE',
        'ROLE_CAPORAL',
        'ROLE_MAJOR',
        'ROLE_COMMANDANT',
        'ROLE_COLONEL',
        'ROLE_GENERAL',
    ];
    public $container;

    public function load(ObjectManager $manager)
    {
        foreach ($this->getUsers() as $key => $user) {
            $manager->persist($user);
        }
        $manager->flush();
    }

    public function getUsers()
    {
        $faker = \Faker\Factory::create();

        $user = new User();
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $user->setFirstName($faker->userName());
        $user->setLastName($faker->lastName());
        $user->setUsername('user');
        $user->setSalt('user');
        $pass = $encoder->encodePassword('azerty', $user->getSalt());
        $user->setPassword($pass);
        $user->setRoles($this->roles);
        yield $user;

        foreach (range(1, 20) as $cmpt) {
            $user = new User();
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setUsername($faker->userName());
            $user->setSalt('azerty');
            $pass = $encoder->encodePassword($faker->randomElement(['azerty']), $user->getSalt());
            $user->setPassword($pass);
            $user->setRoles(array_merge($faker->randomElements($this->roles, 2), ['ROLE_RECRUE']));
            yield $user;
        }
    }

    /**
     * Get the order of this fixture
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
