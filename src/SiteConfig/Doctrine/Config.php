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
namespace SiteConfig\Doctrine;

use SiteConfig\ConfigInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\ParameterTypeInferer;

class Config implements ConfigInterface
{
    /**
     * 
     * @var EntityManager
     */
    protected $entityManager;
    
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
    
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Check if the given $name is set.
     *
     * @param  string $name
     * @return bool
     */
    public function has( $name )
    {
        $sql = sprintf("SELECT %s FROM %s WHERE %s = :%s", $this->valueColumn, $this->table, $this->nameColumn, $this->nameColumn);
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue($this->nameColumn, $name);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if (is_array($result)) {
            return true;
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
        $sql = sprintf("SELECT %s FROM %s WHERE %s = :%s", $this->valueColumn, $this->table, $this->nameColumn, $this->nameColumn);
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue($this->nameColumn, $name);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if (is_array($result)) {
            return $result[ $this->valueColumn ];
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
        if ($this->has($name)) {
            // Update
            $sql = sprintf("UPDATE %s SET %s=:%s WHERE %s=:%s", $this->table, $this->valueColumn, $this->valueColumn, $this->nameColumn, $this->nameColumn);
        } else {
            // Insert
            $sql = sprintf("INSERT INTO %s (%s, %s) VALUES (:%s, :%s)", $this->table, $this->nameColumn, $this->valueColumn, $this->nameColumn, $this->valueColumn);
        }
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue($this->nameColumn, $name);
        $stmt->bindValue($this->valueColumn, $value);
        $result = $stmt->execute();
        
        if ($result === false) {
        	// TODO: Throw exception
        }
        
        return $this;
        
    }
}