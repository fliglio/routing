<?php

namespace Fliglio\Routing;

abstract class Route {
	protected $namespace;
	protected $defaults;
	protected $capturedArgs = array();


	public function __construct(array $defaults) {
		$this->defaults = $defaults;
	}

	abstract public function match($input);

	abstract public function urlFor(array $options = array());

	public function setProtocol( $val ) {  $this->defaults['protocol'] = $val; }

	public function getProtocol() {
		if( isset( $this->defaults['protocol'] ) ) {
			return $this->defaults['protocol'] == '' ? Web_HttpAttributes::getProtocol() : $this->defaults['protocol'];
		} else {
			return Web_HttpAttributes::getProtocol();
		}
	}
	public function getNamespace() {       return $this->namespace; }


	public function getCapturedParams() {  return $this->capturedArgs; }
	public function getParams() {
		$args = $this->capturedArgs;
		foreach( $this->defaults AS $key => $val ) {
			if( !isset( $args[$key] ) || $args[$key] == '' ) {
				$args[$key] = $val;
			}
		}
		$args['ns'] = $this->namespace;
		if( isset( $args['cmd'] ) ) {
			list( $args['module'], $args['commandGroup'], $args['command'] ) = explode( '.', $args['cmd'] );
			unset( $args['cmd'] );
		}
		return $args;
	}

	protected function assembleUrl( $base, array $params ) {
		$length = count( $params );
		foreach( $params AS $key => $val ) {
			if( isset( $this->defaults[$key] ) && $this->defaults[$key] == $val ) {
				unset( $params[$key] );
			}
		}
		if( count( $params ) > 0 ) {
			$cleanParams = array_map( array( $this, 'urlEncodeParts'), array_keys( $params ), array_values( $params ) );
			$queryString = implode( "&", $cleanParams );

			$base .= "?" . $queryString;
		}
		return new Web_Uri( $base );
	}
	private function urlEncodeParts( $key, $val ) {
		return urlencode( $key ) . "=" . urlencode( $val );
	}

}