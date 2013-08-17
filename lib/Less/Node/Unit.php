<?php

namespace Less\Node;

class Unit{
	var $numerator = array();
	var $denominator = array();

	function __construct($numerator = array(), $denominator = array()){
		$this->numerator = $numerator;
		$this->denominator = $denominator;
		sort($this->numerator);
		sort($this->denominator);
	}

	function toCSS(){
		$css = implode('*',$this->numerator);
		for( $i = 0; $i < count($this->denominator); $i++ ){
			$css += '/'.$this->denominator[$i];
		}

		return $css;
	}

	function compare($other) {
		return $this->is( $other->toCSS() ) ? 0 : -1;
	}

	function is($unitString){
		return $this->toCSS() === $unitString;
	}

	function isAngle() {
		return isset( \Less\Node\UnitConversions::$angle[$this->toCSS()] );
	}

	function isEmpty(){
		return count($this->numerator) === 0 && count($this->denominator) === 0;
	}

	function map($callback){

		for($i=0; $i < count($this->numerator); $i++ ){
			$this->numerator[$i] = call_user_func($callback, $this->numerator[$i],false);
		}

		for($i=0; $i < count($this->denominator); $i++ ){
			$this->denominator[$i] = call_user_func($callback, $this->denominator[$i],false);
		}

	}

	function usedUnits(){
		$result = array();

		foreach(\Less\Node\UnitConversions::$groups as $groupName){
			$group = \Less\Node\UnitConversions::${$groupName};

			for($i=0; $i < count($this->numerator); $i++ ){
				$atomicUnit = $this->numerator[$i];
				if( isset($group[$atomicUnit]) && !isset($result[$groupName]) ){
					$result[$groupName] = $atomicUnit;
				}
			}

			for($i=0; $i < count($this->denominator); $i++ ){
				$atomicUnit = $this->denominator[$i];
				if( isset($group[$atomicUnit]) && !isset($result[$groupName]) ){
					$result[$groupName] = $atomicUnit;
				}
			}
		}

		return $result;
	}

	function cancel(){
		$counter = array();

		for ($i = 0; $i < count($this->numerator); $i++) {
			$atomicUnit = $this->numerator[$i];
			$counter[$atomicUnit] = ( isset($counter[$atomicUnit]) ? $counter[$atomicUnit] : 0) + 1;
		}

		for ($i = 0; $i < count($this->denominator); $i++) {
			$atomicUnit = $this->denominator[$i];
			$counter[$atomicUnit] = ( isset($counter[$atomicUnit]) ? $counter[$atomicUnit] : 0) - 1;
		}

		$this->numerator = array();
		$this->denominator = array();


		foreach($counter as $atomicUnit => $count){
			if( $count > 0 ){
				for( $i = 0; $i < $count; $i++ ){
					$this->numerator[] = $atomicUnit;
				}
			}elseif( $count < 0 ){
				for( $i = 0; $i < -$count; $i++ ){
					$this->denominator[] = $atomicUnit;
				}
			}
		}

		sort($this->numerator);
		sort($this->denominator);
	}


}
