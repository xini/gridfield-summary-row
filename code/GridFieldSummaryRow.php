<?php

class GridFieldSummaryRow implements GridField_HTMLProvider {
    
    protected $displayFields = array();

	public function __construct($fragment = 'footer') {
		$this->fragment = $fragment;
	}
	
	public function setDisplayFields($fields) {
	    if(!is_array($fields)) {
	        throw new InvalidArgumentException('
				Arguments passed to GridFieldSummaryRow::setDisplayFields() must be an array');
	    }
	    $this->displayFields = $fields;
	    return $this;
	}
	
	public function getDisplayFields($gridField) {
	    if(!$this->displayFields) {
	        return singleton($gridField->getModelClass())->summaryFields();
	    }
	    return $this->displayFields;
	}
	

	function getHTMLFragments($gridField) {

		Requirements::css("gridfield-summary-row/css/summary-row.css");

		$columns = $gridField->getColumns();
		$list = $gridField->getList();

		$summary_values = new ArrayList();
		
		foreach($columns as $column) {
		    
		    if (!$this->displayFields)
		    {
		        
    			$db = singleton($list->dataClass)->db();
    
    			if(singleton($list->dataClass)->hasField($column)){
    				if($db[$column] == "Money") {
    					$summary_value = $list->sum($column."Amount");
    				} else {
    					$summary_value = $list->sum($column);
    				}
    	        }
    	        else
    	        {
    	        	$summary_value = "";
    	        }
    	        
		    }
		    else 
		    {
		        $class = singleton($list->dataClass);
		        
		        if (key_exists($column, $this->displayFields)) {
		            if ($class->hasField($column)) {
		                if($class->db()[$column] == "Money") {
		                    $summary_value = $list->sum($column."Amount");
		                } else {
		                    $summary_value = $list->sum($column);
		                }
		            } else {
    		            $sum = 0;
    		            foreach ($list as $record) {
    		                $sum += $gridField->getDataFieldValue($record, $column);
    		            }
    		            $summary_value = $sum;
		            }
		            
		            // format
		            $formatClass = $this->displayFields[$column];
		            $obj = $formatClass::create();
		            $obj->setValue($summary_value);
		            $summary_value = $obj->Nice();
		        } else {
		            $summary_value = "";
		        }
		    }

	        $summary_values->push(new ArrayData(array(
				"Value" => $summary_value
			)));
			
		}
		
		$data = new ArrayData(array(
			'SummaryValues' => $summary_values
		));

		return array(
			$this->fragment => $data->renderWith('GridFieldSummaryRow')
		);
	}
}