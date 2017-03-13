<?php
namespace Application\Form\Factory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractFactory implements AbstractFactoryInterface
{
	public function canCreateServiceWithName( ServiceLocatorInterface $serviceLocator, $name, $requestedName)
	{
		return $requestedName !== 'Application\Form\Form' &&
			strpos($requestedName, 'Application\\Form\\') === 0 &&
			class_exists($requestedName)
		? true : false;
	}
	
	public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
	{
		return new $requestedName();
	}
}