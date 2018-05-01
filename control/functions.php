<?php

function in_array_field($needle, $needle_field, $haystack, $strict = false) { 
	if ($strict) { 
		foreach ($haystack as $item) 
			if (isset($item->$needle_field) && $item->$needle_field === $needle) 
				return true; 
	} 
	else { 
		foreach ($haystack as $item) 
			if (isset($item->$needle_field) && $item->$needle_field == $needle) 
				return true; 
	} 
	return false; 
}

?>