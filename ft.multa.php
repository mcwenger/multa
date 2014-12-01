<?php

class Fieldtype_multa extends Fieldtype
{
    
    public $max_entries_limit = 100;
    public $return_limit = 100;
    public $value = 'url'; // purposed for entries only
    
    
/*-- Fieldtype_multa::render ---------------------------------------------------------------- /// */
	
	
    /**
     * Statamic Fieldtype: render field output
     *
     * @return string
     */
    public function render()
    {
        
        // no folder? bail out!
        if ( empty(array_get($this->field_config, 'folder')) ) return "<p class=\"error\">The 'folder' setting is either missing or malformed.</p>";
        
        /*
        |--------------------------------------------------------------------------
        | Vars
        |--------------------------------------------------------------------------
        |
        | Let's define all of our top-level variables
        |
        */
		
		$fieldtype             = array_get($this->field_config, 'type');
        $field_data            = $this->field_data;
        $max_entries           = array_get($this->field_config, 'max_entries', $this->max_entries_limit);
        $multiple              = ($max_entries === 1) ? false : true;
        $multiple_array_holder = $multiple ? '[]' : '';
        $folder                = array_get($this->field_config, 'folder');
        $label                 = array_get($this->field_config, 'label', 'title');
        $value                 = $this->value; 
        $entries               = $this->_get_content($folder,$label,$this->value,true);
        $sort_by               = array_get($this->field_config, 'sort_by');
        $sort_dir              = array_get($this->field_config, 'sort_dir', 'asc');
        $multiple_folders      = strpos($folder,'|') ? true : false;
        $show_folder           = ($multiple_folders && array_get($this->field_config, 'show_folder')) ? true : false;
		$show_date             = ($multiple_folders && array_get($this->field_config, 'show_date')) ? true : false;
		$count                 = 0;
		
        /*
        |--------------------------------------------------------------------------
        | Field HTML
        |--------------------------------------------------------------------------
        |
        | Generate the HTML for the field
        |
        */
        
        $html = '<div class="multa-blocks group" id="'. $this->field_id .'">';
        $html .= '<div class="util"><span class="errors"></span><div class="search"><input type="text" autocomplete="off" placeholder="Filter entries"></div></div>';
        $html .= '<div class="multa-block rel-options">';
        
        // relationship options block
        foreach($entries as $url=>$entry)
        {
			$count++;

            // is the index output active?
            $active = ( is_array($field_data) && $this->_in_multiarray($url, $field_data, $this->value) ) ? ' class="active" ' : '';
			
            $list_item_label = ( isset($entry[$label]) ) ? $entry[$label] : $url;
            $list_show_date = ( $show_date && isset($entry['date']) ) ? '<span class="date">'.$entry['date'].'</span>'  : '';
            $item_label = ( $multiple_folders && $show_folder && isset($entry['_folder'])) ? '<span class="folder">'. $entry['_folder'] .'</span>' . $list_item_label . $list_show_date : $list_item_label . $list_show_date;

			$html .= '<label '. $active .' for="' . $this->field_id . '_' . $count . '" data-name="'. $this->fieldname . $multiple_array_holder .'" data-value="'. $url .'">' . $item_label .'</label>';
			
        }
        
        $html .= '</div>';
        $html .= '<div class="multa-block rel-made" data-max_entries="'. $max_entries .'">';
        
        // made relationships block
        if ($field_data)
        {
	        foreach($field_data as $index=>$entry)
	        {
            	
            	$list_item_label = ( isset($entries[$entry[$this->value]][$label]) ) ? $entries[$entry[$this->value]][$label] : $entry[$this->value];
				$list_show_date = ( $show_date && isset($entries[$entry[$this->value]]['date']) ) ? '<span class="date">'.$entries[$entry[$this->value]]['date'].'</span>'  : '';
            	$item_label = ( $multiple_folders && $show_folder && isset($entries[$entry[$this->value]]['_folder'])) ? '<span class="folder">'. $entries[$entry[$this->value]]['_folder'] .'</span>' . $list_item_label . $list_show_date : $list_item_label . $list_show_date;
				
				$html .= '<label class="active" for="' . $this->field_id . '_' . $count . '" data-value="'. $entry[$this->value] .'"><span class="handle"></span>' . $item_label .'<input type="hidden" name="'. $this->fieldname . $multiple_array_holder .'" value="'. $entry[$this->value] .'"></label>';
	        }
        }
        
        $html .= '</div>';
        $html .= '</div> <!--/ multa-blocks-->';
        $html .= '<p class="notes notes-below">sorted by: '. ($sort_by ? $sort_by : 'number/date') .', '. $sort_dir .'</p>';
        
        return $html;
	
	} // end: render


/*-- Fieldtype_multa::process ---------------------------------------------------------------- /// */


    /**
     * Statamic Fieldtype: hook to process the data prior to saving
     *
     * @param string  $settings  field settings as set in fieldsets:field
     * @return array
     */
    public function process($settings)
    {
        // If empty, save as null
        if ($this->field_data === '') {
			return null;
        }

        $folder       = array_get($settings, 'folder');
        $label        = array_get($settings, 'label', 'title');
		$value        = $this->value;
        $max_entries  = array_get($this->field_config, 'max_entries', $this->max_entries_limit);
		$entries      = $this->_get_content($folder,$label,$value,true);
        $save         = array_get($settings, 'save');
        $save         = (!empty($save) && strpos($save,'|')) ? explode('|', $save) : Helper::ensureArray($save);
		$save_options = array('title','date','datestamp','slug');
		
        foreach($this->field_data as $key=>$value)
        {
			// format default data
			$this->field_data[$key] = array(
				'url' => $value
			);
	        
	        // add save options to data
			if (isset($save))
			{
				foreach($save as $i=>$field)
				{
					// check if we allow the saving of this field
					if (in_array($field, $save_options) && is_array($entries[$value]))
					{
						
						$this->field_data[$key][$field] = $entries[$value][$field];
					}
				}
			}
        }

        return $this->field_data;
	
	} // end: process


/*-- Helpers ---------------------------------------------------------------- /// */
    
    
    /**
     * Sift through an array to check for an array value
     *
     * @param string  $elem  value of array key
     * @param string  $array  array to check against
     * @param string  $field  array key
     * @return bool
     */
	private function _in_multiarray($elem, $array, $field)
	{
	    $top = sizeof($array) - 1;
	    $bottom = 0;
	    while($bottom <= $top)
	    {
	        if($array[$bottom][$field] == $elem)
	        {
	            return true;
	        }
	        else 
	        {
	            if(is_array($array[$bottom][$field]))
	                if(_in_multiarray($elem, ($array[$bottom][$field])))
	                    return true;
			}
	        $bottom++;
	    }        
	    
	    return false;
	
	} // end: _in_multiarray
    
    
    /**
     * Get content from folder(s)
     *
     * @param string  $folder  where should we grab content from?
     * @param string  $label  
     * @param string  $value  
     * @return array
     */
	private function _get_content($folder,$label,$value,$all_data=false)
	{
        
        // no folder? bail out!
		if (empty($folder)) return array();
		
		$sort_by     = array_get($this->field_config, 'sort_by');
		$sort_dir    = array_get($this->field_config, 'sort_dir', 'asc');
		$return_limit = array_get($this->field_config, 'return_limit', $this->return_limit);

        $entries = array();
        $content_set = ContentService::getContentByFolders(array($folder));

		// let's be consistent w/ multa's cousin, Suggest(a), and the core filter options
        $content_set->filter(
			array(
                'show_hidden' => array_get($this->field_config, array('show_hidden'), false),
                'show_drafts' => array_get($this->field_config, 'show_drafts', false),
                'since'       => array_get($this->field_config, 'since'),
                'until'       => array_get($this->field_config, 'until'),
                'show_past'   => array_get($this->field_config, 'show_past', true),
                'show_future' => array_get($this->field_config, 'show_future', true),
                'type'        => array_get($this->field_config, 'content_type', 'entries'),
                'conditions'  => trim(array_get($this->field_config, 'conditions'))
            )
        );
        
        // Order and sort
        $content_set->sort($sort_by, $sort_dir);
        
        // Go get 'em!
        $queried_entries = $content_set->get();
        
        foreach ($queried_entries as $entry) {
            
            // do we need all data per entry?
            if ($all_data)
            {
	            if (isset($entry['url'])) {
	            	$entries[$entry['url']] = $entry;
	            }
	            // remove some stuff
	            unset(
	            	$entries[$entry['url']]['dashboard_main_content'], 
	            	$entries[$entry['url']]['dashboard_sidebar_content']
	            );
            }
            // or just a simple array?
            else
            {
	            $pieces = array();
	            foreach (Helper::ensureArray($label) as $label_part) {
	                if (isset($entry[$label_part]) && isset($entry[$value])) {
	                    $pieces[] = $entry[$label_part];
	                }
	            }
	
	            $entries[$entry[$value]] = join(' Ð ', $pieces);
            }
        }
        
        // slice to limit and return
        return array_slice($entries, 0, $return_limit, true);
	
	} // end: _get_content
    
    
    
}
