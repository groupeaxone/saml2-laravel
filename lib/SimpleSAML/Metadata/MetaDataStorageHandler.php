<?php

/**
 * This file defines a class for metadata handling.
 *
 * @author Andreas Åkre Solberg, UNINETT AS. <andreas.solberg@uninett.no>
 * @package simpleSAMLphp
 * @version $Id$
 */ 
class SimpleSAML_Metadata_MetaDataStorageHandler {


	/**
	 * This static variable contains a reference to the current
	 * instance of the metadata handler. This variable will be NULL if
	 * we haven't instantiated a metadata handler yet.
	 */
	private static $metadataHandler = NULL;


	/**
	 * This is a list of all the metadata sources we have in our metadata
	 * chain. When we need metadata, we will look through this chain from start to end.
	 */
	private $sources;


	/**
	 * This function retrieves the current instance of the metadata handler.
	 * The metadata handler will be instantiated if this is the first call
	 * to this fuunction.
	 *
	 * @return The current metadata handler instance.
	 */
	public static function getMetadataHandler() {
		if(self::$metadataHandler === NULL) {
			self::$metadataHandler = new SimpleSAML_Metadata_MetaDataStorageHandler();
		}

		return self::$metadataHandler;
	}


	/**
	 * This constructor initializes this metadata storage handler. It will load and
	 * parse the configuration, and initialize the metadata source list.
	 */
	protected function __construct() {

		$config = SimpleSAML_Configuration::getInstance();

		$sourcesConfig = $config->getValue('metadata.sources', NULL);

		/* For backwards compatibility, and to provide a default configuration. */
		if($sourcesConfig === NULL) {
			$type = $config->getValue('metadata.handler', 'flatfile');
			$sourcesConfig = array(array('type' => $type));
		}

		if(!is_array($sourcesConfig)) {
			throw new Exception(
				'Invalid configuration of the \'metadata.sources\' configuration option.' .
				' This option should be an array.'
				);
		}

		try {
			$this->sources = SimpleSAML_Metadata_MetaDataStorageSource::parseSources($sourcesConfig);
		} catch (Exception $e) {
			throw new Exception('Invalid configuration of the \'metadata.sources\'' .
				' configuration option: ' . $e->getMessage());
		}

	}


	/**
	 * This function is used to generate some metadata elements automatically.
	 *
	 * @param $property  The metadata property which should be autogenerated.
	 * @param $set  The set we the property comes from.
	 * @return The autogenerated metadata property.
	 */
	public function getGenerated($property, $set = 'saml20-sp-hosted', $options = array() ) {

		/* First we check if the user has overridden this property in the metadata. */
		try {
			$metadataSet = $this->getMetaDataCurrent($set);
			if(array_key_exists($property, $metadataSet)) {
				return $metadataSet[$property];
			}
		} catch(Exception $e) {
			/* Probably metadata wasn't found. In any case we continue by generating the metadata. */
		}
		
		/* Get the configuration. */
		$config = SimpleSAML_Configuration::getInstance();
		assert($config instanceof SimpleSAML_Configuration);
		
		$baseurl = SimpleSAML_Utilities::selfURLhost() . '/' . 
			$config->getBaseURL();
		
		if ($set == 'saml20-sp-hosted') {
			switch ($property) {				
				case 'AssertionConsumerService' : 
					return $baseurl . 'saml2/sp/AssertionConsumerService.php';

				case 'SingleLogoutService' : 
					return $baseurl . 'saml2/sp/SingleLogoutService.php';					
			}
		} elseif($set == 'saml20-idp-hosted') {
			
			$logouttype = 'traditional';
			if (array_key_exists('logouttype', $options)) $logouttype = $options['logouttype'];
			if (!in_array($logouttype, array('traditional', 'iframe'))) 
				throw new Exception('Invalid logout type [' . $logouttype . '] in IdP Hosted Metadata');

			switch ($property) {				
				case 'SingleSignOnService' : 
					return $baseurl . 'saml2/idp/SSOService.php';

				case 'SingleLogoutService' : 
					
					switch ($logouttype) {
						case 'iframe' : 
							return $baseurl . 'saml2/idp/SingleLogoutServiceiFrame.php';
						
						case 'traditional' :
						default :
							return $baseurl . 'saml2/idp/SingleLogoutService.php';			
					}
				
				case 'SingleLogoutServiceResponse' : 

					switch ($logouttype) {
						case 'iframe' : 
							return $baseurl . 'saml2/idp/SingleLogoutServiceiFrameResponse.php';
						
						case 'traditional' :
						default :
							return $baseurl . 'saml2/idp/SingleLogoutService.php';			
					}
				
			}
		} elseif($set == 'shib13-sp-hosted') {
			switch ($property) {				
				case 'AssertionConsumerService' : 
					return $baseurl . 'shib13/sp/AssertionConsumerService.php';
			}
		} elseif($set == 'shib13-idp-hosted') {
			switch ($property) {				
				case 'SingleSignOnService' : 
					return $baseurl . 'shib13/idp/SSOService.php';			
			}
		} elseif($set == 'openid-provider') {
			switch ($property) {				
				case 'server' : 
					return $baseurl . 'openid/provider/server.php';			
			}
		}
		
		throw new Exception('Could not generate metadata property ' . $property . ' for set ' . $set . '.');
	}


	/**
	 * This function lists all known metadata in the given set. It is returned as an associative array
	 * where the key is the entity id.
	 *
	 * @param $set  The set we want to list metadata from.
	 * @return An associative array with the metadata from from the given set.
	 */
	public function getList($set = 'saml20-idp-remote') {

		assert('is_string($set)');

		$result = array();

		foreach($this->sources as $source) {
			$srcList = $source->getMetadataSet($set);

			/* $result is the last argument to array_merge because we want the content already
			 * in $result to have precedence.
			 */
			$result = array_merge($srcList, $result);
		}

		return $result;
	}


	/**
	 * This function retrieves metadata for the current entity based on the hostname/path the request
	 * was directed to. It will throw an exception if it is unable to locate the metadata.
	 *
	 * @param $set  The set we want metadata from.
	 * @return An associative array with the metadata.
	 */
	public function getMetaDataCurrent($set = 'saml20-sp-hosted') {
		return $this->getMetaData(NULL, $set);
	}


	/**
	 * This function locates the current entity id based on the hostname/path combination the user accessed.
	 * It will throw an exception if it is unable to locate the entity id.
	 *
	 * @param $set  The set we look for the entity id in.
	 * @param $type Do you want to return the metaindex or the entityID. [entityid|metaindex]
	 * @return The entity id which is associated with the current hostname/path combination.
	 */
	public function getMetaDataCurrentEntityID($set = 'saml20-sp-hosted', $type = 'entityid') {

		assert('is_string($set)');

		/* First we look for the hostname/path combination. */
		$currenthostwithpath = SimpleSAML_Utilities::getSelfHostWithPath(); // sp.example.org/university

		foreach($this->sources as $source) {
			$index = $source->getEntityIdFromHostPath($currenthostwithpath, $set, $type);
			if($index !== NULL) {
				return $index;
			}
		}

	
		/* Then we look for the hostname. */
		$currenthost = SimpleSAML_Utilities::getSelfHost(); // sp.example.org
		if(strpos($currenthost, ":") !== FALSE) {
			$currenthostdecomposed = explode(":", $currenthost);
			$currenthost = $currenthostdecomposed[0];
		}

		foreach($this->sources as $source) {
			$index = $source->getEntityIdFromHostPath($currenthost, $set, $type);
			if($index !== NULL) {
				return $index;
			}
		}


		/* Then we look for the DEFAULT entry. */
		foreach($this->sources as $source) {
			$entityId = $source->getEntityIdFromHostPath('__DEFAULT__', $set, $type);
			if($entityId !== NULL) {
				return $entityId;
			}
		}



		/* We were unable to find the hostname/path in any metadata source. */
		throw new Exception('Could not find any default metadata entities in set [' . $set . '] for host [' . $currenthost . ' : ' . $currenthostwithpath . ']');
	}
	
	/**
	 * This method will call getPreferredEntityIdFromCIDRhint() on all of the
	 * sources.
	 *
	 * @param $set  Which set of metadata we are looking it up in.
	 * @param $ip	IP address
	 * @return The entity id of a entity which have a CIDR hint where the provided
	 * 		IP address match.
	 */
	public function getPreferredEntityIdFromCIDRhint($set, $ip) {
	
		foreach($this->sources as $source) {
			$entityId = $source->getPreferredEntityIdFromCIDRhint($set, $ip);
			if($entityId !== NULL) {
				return $entityId;
			}
		}
		
		return NULL;
		
	}

	/**
	 * This function looks up the metadata for the given entity id in the given set. It will throw an
	 * exception if it is unable to locate the metadata.
	 *
	 * @param $index  The entity id we are looking up. This parameter may be NULL, in which case we look up
	 *                   the current entity id based on the current hostname/path.
	 * @param $set  The set of metadata we are looking up the entity id in.
	 */
	public function getMetaData($index, $set = 'saml20-sp-hosted') {

		assert('is_string($set)');

		if($index === NULL) {
			$index = $this->getMetaDataCurrentEntityID($set, 'metaindex');
		}

		assert('is_string($index)');

		foreach($this->sources as $source) {
			$metadata = $source->getMetaData($index, $set);
			
			if($metadata !== NULL) {
				
				if (array_key_exists('expire', $metadata)) {
					if ($metadata['expire'] < time()) {
						throw new Exception('Metadata for the entity [' . $index . '] expired ' . 
							(time() - $metadata['expire']) . ' seconds ago.'
						);
					}
				}
				
				$metadata['metadata-index'] = $index;
				$metadata['metadata-set'] = $set;
				assert('array_key_exists("entityid", $metadata)');
				return $metadata;
			}
		}

		throw new Exception('Unable to locate metadata for \'' . $index . '\' in set \'' . $set . '\'.');
	}

}

?>