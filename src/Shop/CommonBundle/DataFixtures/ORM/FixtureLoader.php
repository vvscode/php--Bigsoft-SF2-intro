<?php
namespace Shop\CommonBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\Doctrine;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shop\SecurityBundle\Entity\User;
use Shop\SecurityBundle\Service\UserServiceInterface;
use Shop\SecurityBundle\Entity\Role;
use Shop\WebSiteBundle\Entity\Product;
use Shop\WebSiteBundle\Service\ProductService;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FixtureLoader implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadUsers();
        $this->loadProducts();
    }

    /**
     * Load products fixtures
     */
    private function loadProducts()
    {
        /**
         * @var ProductService
         */
        $productService = $this->container->get('shop.website.product_service');

        // Load fake items from txt file
        $fileName = sprintf('%sproducts.json',$this->container->getParameter('shop.common.fixtures_path'));
        $json = file_get_contents($fileName);
        $arr = json_decode($json, true);

        foreach($arr as $item){
            $product = $productService->createProduct($item);
            $this->manager->persist($product);
        }

        $this->manager->flush();
    }

    /**
     * Load users fixtures
     */
    private function loadUsers()
    {
        /**
         * @var UserServiceInterface
         */
        $userService = $this->container->get('shop.security.user_service');

        // создание пользователя
        $adminRole = new Role();
        $adminRole->setName('ROLE_ADMIN');
        $this->manager->persist($adminRole);
        $userRole = new Role();
        $userRole->setName('ROLE_USER');
        $this->manager->persist($userRole);

        /** @var $user User  */
        $user = $userService->createUser('admin', 'admin');
        $user->getUserRoles()->add($userRole);
        $user->getUserRoles()->add($adminRole);
        $this->manager->persist($user);

        $userRole = new Role();
        $userRole->setName('ROLE_USER');
        $this->manager->persist($userRole);

        /** @var $user User  */
        $user = $userService->createUser('user', 'user');
        $user->getUserRoles()->add($userRole);
        $this->manager->persist($user);

        $this->manager->flush();
    }
}
