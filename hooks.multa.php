<?php

class Hooks_multa extends Hooks
{
    // add custom css to head
    public function control_panel__add_to_head() {
        
		if (URL::getCurrent(false) == '/publish') {
	        
	        $css = $this->css->link('multa.css');
	        
	        return $css;
	        
        }        
    }
    
    // add custom js to foot
    public function control_panel__add_to_foot() {
        
		if (URL::getCurrent(false) == '/publish') {
        
	        $return = '';
	        
	        $js = array(
	        	'selecta.jquery-ui-1.11.2.js',
	        	'multa.js'
	        );
	        foreach($js as $file)
	        {
		        $return .= $this->js->link($file)."\n";
	        }
	        
	        return $return;
		
		}
    }
}
