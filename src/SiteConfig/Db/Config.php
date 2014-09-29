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
namespace SiteConfig\Db;

use Zend\Db\Adapter\Adapter;
use SiteConfig\ConfigInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;

class Config implements ConfigInterface
{
    /**
     * @var Adapter
     */
	protected $adapter;
	
	/**
	 * 
	 * @var Sql
	 */
	protected $sql;
	
	/** 
	 * @var string
	 */
	protected $table = 'options';
	
	/**
	 * 
	 * @var string
	 */
	protected $nameColumn = 'name';
	
	/**
	 * 
	 * @var string
	 */
	protected $valueColumn = 'value';
	
	public function __construct($adapter, $options = array())
	{
	    // table
	    if (isset($options['table'])) {
    	    if (!(is_string($options['table']) || $options['table'] instanceof TableIdentifier)) {
    	        throw new Exception\InvalidArgumentException('Table name must be a string or an instance of Zend\Db\Sql\TableIdentifier');
    	    }
    	    $this->table = $options['table'];
	    }
	    
	    // Adapter
		$this->adapter = $adapter;
		
		// Create SQL
		$this->sql = new Sql($this->adapter, $this->table);
	}
	
	public static function factory($options = array())
	{
		
	}
	
	protected function getSelectStatement( $name )
	{
	    $platform    = $this->adapter->getPlatform();
	    $nameColumn  = $platform->quoteIdentifier( $this->nameColumn );
	    $valueColumn = $platform->quoteIdentifier( $this->valueColumn );
	    $paramName   = $this->adapter->getDriver()->formatParameterName( $this->nameColumn );
	    $sql         = sprintf("SELECT %s FROM %s WHERE %s=%s", $valueColumn, $this->table, $nameColumn, $paramName);
	    
	    return  $this->adapter->createStatement($sql);
	}
	
    /**
     * Check if the given $name is set.
     *
     * @param  string $name
     * @return bool
     */
    public function has( $name )
    {
        $statement   = $this->getSelectStatement($name);
        $results     = $statement->execute( array($this->nameColumn => $name));
        
        if ($results) {
            $row = $results->current();
            if (!empty($row)) {
            	return true;
            }
        }
         
        return false;
    }
    
    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get( $name, $default = null )
    {
        $nameParam   = $this->adapter->getDriver()->formatParameterName( $this->nameColumn );
        $statement   = $this->getSelectStatement($name);
        $results     = $statement->execute( array(
            $nameParam => $name
        ));
        
        if ($results) {
            $row = $results->current();
            
            $value = $row[$this->valueColumn]; 
            $unserialized = @unserialize( $value );
            if (($unserialized === false) && ( strcasecmp($value, serialize(false)) !== 0)) {
            	return $value;
            } else {
            	return $unserialized;
            }
        }
                       
    	return $default;
    }
    
    /**
     * Set a value for the given $name.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return ConfigInterface
     */
    public function set( $name, $value)
    {
        
        $platform    = $this->adapter->getPlatform();
        $nameColumn  = $platform->quoteIdentifier( $this->nameColumn );
        $valueColumn = $platform->quoteIdentifier( $this->valueColumn );     
        $nameParam   = $this->adapter->getDriver()->formatParameterName( $this->nameColumn );
        $valueParam  = $this->adapter->getDriver()->formatParameterName( $this->valueColumn );
        
    	if ($this->has($name)) {
    		// Update
    	    $sql = sprintf("UPDATE  %s SET %s=%s WHERE %s=%s", $this->table, $valueColumn, $valueParam, $nameColumn, $nameParam);
    	} else {
    		// Insert
    	    $sql = sprintf("INSERT INTO %s (%s, %s) VALUES (%s, %s)", $this->table, $nameColumn, $valueColumn, $nameParam, $valueParam);
    	}
    	$statement = $this->adapter->createStatement($sql);
    	
    	$results = $statement->execute( array(
    	    $nameParam  => $name,
    	    $valueParam => serialize($value)
    	));
    	
    	return $this;
    }
    
}
