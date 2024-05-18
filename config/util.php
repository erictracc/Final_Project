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

// Function to generate password hash
function setPasswordHash($pass): string
{
    return password_hash($pass, PASSWORD_DEFAULT);
}

// Function to generate success message
function success($text): string
{
    return "<div style='text-align:center;padding-bottom:5px;color:green;'>$text</div>";
}

// Function to generate failure message
function fail($text): string
{
    return "<div style='text-align:center;padding-bottom:5px;color:#FF0019;'>$text</div>";
}

