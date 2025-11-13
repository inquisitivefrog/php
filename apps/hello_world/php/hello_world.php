<?php
// PHP "Hello World" Application
// This script now demonstrates a simple function and array handling.

/**
 * Greets the user and returns the message.
 * * @param string $name The name to greet.
 * @return string The completed greeting string.
 */
function get_greeting(string $name = "Engineer"): string {
    // String interpolation using double quotes
    return "Hello, Dockerized PHP World, specifically the $name!";
}

// 1. Output the main greeting using the custom function
$greeting = get_greeting("Software Engineer");
echo "<h1>" . $greeting . "</h1>";

// 2. Display the PHP version
echo "<p>This page was successfully served by PHP version: " . phpversion() . "</p>";


// 3. Simple Array and Loop Example
$languages = ["Python", "Golang", "C++", "PHP"];

echo "<h2>Your Recent Language Tour</h2>";
echo "<p>You've recently been switching between:</p>";
echo "<ul>";
foreach ($languages as $language) {
    // Array access is implicit in the foreach loop
    echo "<li>$language</li>";
}
echo "</ul>";

// 4. Using the print_r() function for debugging (useful for quick variable inspection)
echo "<h3>Debugging Check:</h3>";
echo "<p>The \$languages array structure:</p>";
echo "<pre>";
print_r($languages);
echo "</pre>";

?>
