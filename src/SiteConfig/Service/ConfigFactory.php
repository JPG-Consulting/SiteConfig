<?php
/**
 * SiteConfig
 *
 * ZF-2 Database driven site configuration
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @author Juan Pedro Gonzalez Gutierrez
 * @copyright Copyright (c) 2013 Juan Pedro Gonzalez Gutierrez (http://www.jpg-consulting.com)
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 License
 */
namespace SiteConfig\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SiteConfig\Db\Config;
use SiteConfig\Doctrine\Config as DoctrineConfig;
use Zend\Db\Adapter\AdapterInterface;
use SiteConfig\Exception;
use Doctrine\ORM\EntityManager;

class ConfigFactory implements FactoryInterface
{

	public function createService(ServiceLocatorInterface $serviceLocator)
	{
	    $config = $serviceLocator->has('Config') ? $serviceLocator->get('Config') : array();
	    $config = isset($config['site_config']) ? $config['site_config'] : array();
	    
	    // Sanity checks
	    if (isset($config['entity_manager']) && isset($config['adapter'])) {
	    	// TODO: Throw exception only one type can be set at a time!!!!
	    	throw new Exception\RuntimeException("Zend DB and Doctrine cannot be configured at the same time.");
	    }
	    
	    // Zend DB configured
	    if (isset($config['adapter'])) {
	        if ( !$serviceLocator->has( $config['adapter']) ) {
	            throw new Exception\InvalidArgumentException("adapter must be a valid service name returning an object that implements Zend\Db\AdapterInterface");            
	        }
	         
	        $adapter = $serviceLocator->get($config['adapter']);
	        if (!($adapter instanceof \Zend\Db\Adapter\AdapterInterface)) {
	            throw new Exception\InvalidArgumentException("adapter must be a valid service name returning an object that implements Zend\Db\AdapterInterface");
	        }
	        return new Config($adapter, $config);
	    }
	    
	    // Doctrine configured
	    if (isset($config['entity_manager'])) {
	        if ( !$serviceLocator->has( $config['entity_manager']) ) {
	        	throw new Exception\InvalidArgumentException("entity_manager must be a valid service name returning an object that implements Doctrine\ORM\EntityManager.");
	        }
	            
	        $entityManager = $serviceLocator->get($config['entity_manager']);
	        if (!($entityManager instanceof \Doctrine\ORM\EntityManager)) {
	            throw new Exception\InvalidArgumentException("entity_manager must be a valid service name returning an object that implements Doctrine\ORM\EntityManager.");
	        }
	        return new DoctrineConfig($entityManager, $config);
	    }
	    
	    // Lazy loading...
	    // ...try Zend DB
	    if ($serviceLocator->has('Zend\Db\Adapter\Adapter')) {
	        // Load the Zend DB Config
	        $adapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
	        if ($adapter instanceof \Zend\Db\Adapter\AdapterInterface) {
	           return new Config($adapter, $config);
	        }
	    }
	    
	    // ...try Doctrine
	    if ($serviceLocator->has('doctrine.entitymanager.orm_default')) {
	        // Load the Zend DB Config
	        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
	        if ($entityManager instanceof \Doctrine\ORM\EntityManager) {
	           return new DoctrineConfig($entityManager, $config);
	        }
	    }
	    
	    // Something went wrong
	    throw new Exception\RuntimeException("No Zend DB or Doctrine connection found.");	    
	}
}