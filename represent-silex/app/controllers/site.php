<?php

/**
 * "root"
 */
$app->get("/", function() use($app) {
        $twigvars = array();

// Basic Vars
        $twigvars['title'] = "Map of the Hawaii Startup Community";
        $twigvars['responsive'] = true;
        $twigvars['addmodal'] = true;
        $twigvars['coworking'] = 0;
        $twigvars['investor'] = 0;
        $twigvars['startup'] = 0;
        $twigvars['incubator'] = 0;
        $twigvars['accelerator'] = 0;

// Grab places from the DB
        $sql = "SELECT `title`,`type`,`lat`,`lng`,`description`,`uri`,`address` FROM places WHERE approved = 1";
        $places = $app['db']->fetchAll($sql);
        foreach ($places as $place) {
            $twigvars[$place['type']]++;
        }
        $twigvars['markers'] = json_encode($places);

        return $app['twig']->render('index.twig', $twigvars);
    });