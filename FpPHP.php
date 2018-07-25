<?php

function attr($field){
	return function($data) use ($field){
		return $data[$field];
	};
}

$users = [
	['id' => 1, 'name' => 'Anderson', 'age' => 27],
	['id' => 2, 'name' => 'Celina', 'age' => 25],
	['id' => 3, 'name' => 'Sara', 'age' => 22]
];

$foods = [
	['id' => 1, 'name' => 'Apple', 'price' => 2.70],
	['id' => 2, 'name' => 'Orange', 'price' => 3.20],
	['id' => 3, 'name' => 'Banana', 'price' => 1.40]
];

$usersName = array_map(attr('name'), $users); 
$usersAge = array_map(attr('age'), $users);
$foodPrice = array_map(attr('price'), $foods);

print_r($usersName);
echo "<br>";
print_r($usersAge);
echo "<br>";
print_r($foodPrice);
echo "map filter reduce";

?>