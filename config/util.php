<?php

// Database connection parameters
$serverName = "localhost";
$username = "root";
$password = "";
$dbname = "final_project";

// Establishing database connection
$conn = new mysqli($serverName, $username, $password, $dbname);

// Check if connection was successful
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

// Function to hash passwords using bcrypt with a specified cost
function setPasswordHash($pass): string {
    $options = [
        'cost' => 12,
    ];
    return password_hash($pass, PASSWORD_BCRYPT, $options); // Using bcrypt algorithm explicitly
}

// Function to generate success message
function completed($text): string
{
    return "<div style='text-align:center;padding-bottom:5px;color:green;'>$text</div>";
}

// Function to generate failure message
function failed($text): string
{
    return "<div style='text-align:center;padding-bottom:5px;color:#FF0019;'>$text</div>";
}


