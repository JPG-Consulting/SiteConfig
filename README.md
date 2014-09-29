# SiteConfig #
A simple module to retrieve and set generic website configuration using Zend DB or Doctrine.

The module uses a simple table containing keys and values. The default table is created with the following SQL command:

    CREATE TABLE IF NOT EXISTS `options` (
        `name` varchar(64) NOT NULL,
        `value` text
    )

## Zend DB ##
You must provide the Zend DB service, this is done as follows:

    'site_config' => array(
        'adapter' => 'Zend\Db\Adapter',
    )

Change `Zend\Db\Adapter` to your selected database service.

## Doctrine ##
You must provide the Doctrine DB service, this is done as follows:

    'site_config' => array(
        'entity_manager' => 'doctrine.entitymanager.orm_default',
    )

Change `doctrine.entitymanager.orm_default` to your selected doctrine service.
