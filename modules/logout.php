<?php
use Darkheim\Application\Auth\AuthService;

if(!isLoggedIn()) { redirect(); }

(new AuthService())->logout();

// Redirect to home
redirect();