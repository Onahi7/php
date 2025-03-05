<?php
/**
 * Authentication helper functions
 */

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check if user has role
function has_role($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Function to check if user has any of the given roles
function has_any_role($roles) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    return in_array($_SESSION['user_role'], (array)$roles);
}

// Function to get current user ID
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get current user role
function current_user_role() {
    return $_SESSION['user_role'] ?? null;
}

// Function to get current user name
function current_user_name() {
    return $_SESSION['user_name'] ?? null;
}

// Function to get current user email
function current_user_email() {
    return $_SESSION['user_email'] ?? null;
}

// Function to require authentication
function require_auth() {
    if (!is_logged_in()) {
        flash('Please log in to access this page', 'error');
        redirect('/login');
    }
}

// Function to require specific role
function require_role($role) {
    require_auth();
    if (!has_role($role)) {
        flash('You do not have permission to access this page', 'error');
        redirect('/dashboard');
    }
}

// Function to require any of the given roles
function require_any_role($roles) {
    require_auth();
    if (!has_any_role($roles)) {
        flash('You do not have permission to access this page', 'error');
        redirect('/dashboard');
    }
}
