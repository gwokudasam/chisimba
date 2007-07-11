<?php
/**
*
* Class to parse a string (e.g. page content) that contains a link
* to a timeline and render the timeline in the page
*
* @author Derek Keats
*
*/

class parse4timeline extends object
{
    
    public function init()
    {
        //Nothing to do here
    }
    
    /**
    *
    * Method to parse the string
    * @param String $str The string to parse
    * @return The parsed string
    *
    */
    public function parse($str)
    {
    	//Instantiate the modules class to check if simplemap is registered
    	$objModule = $this->getObject('modules','modulecatalogue');
    	//See if the simple map module is registered and set a param
    	$isRegistered = $objModule->checkIfRegistered('timeline', 'timeline');
    	if ($isRegistered){
	    	//Instantiate the timeline parser
	    	$objTlParser = $this->getObject('timelineparser', 'timeline');
    	}
        preg_match_all('/\\[TIMELINE]<a.*?href="(?P<timelinelink>.*?)".*?>.*?<\/a>\\[\/TIMELINE]/', $str, $results, PREG_PATTERN_ORDER);
        $counter = 0;
        foreach ($results[0] as $item)
        {
        	if ($isRegistered) {
            	$replacement = $objTlParser->getRemote($results['timelinelink'][$counter]);
        	} else {
    			$objLanguage = $this->getObject('language', 'language');
    	    	$replacement = $results[1][$counter] . "<br /><div class=\"error\"><h3>" 
    	      	  . $objLanguage->languageText("mod_filters_error_timelinenotinst", "filters")
    	      	  . "</h3></div>";
        	}
            $str = str_replace($item, $replacement, $str);
            $counter++;
        }
        
        return $str;
    }
}
?>