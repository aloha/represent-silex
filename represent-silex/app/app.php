<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

include_once('controllers/site.php');
include_once('controllers/admin.php');
include_once('controllers/api.php');

$app->get('/login/', function(Request $request) use ($app) {
        return $app['twig']->render('login.twig', array(
                'error' => $app['security.last_error']($request),
                'last_username' => $app['session']->get('_security.last_username'),
            ));
    });

$app->get('/logout/', function(Request $request) use ($app) {
        $app['session']->clear();
    });