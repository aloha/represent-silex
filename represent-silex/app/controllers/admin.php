<?php

$fetchPlaces = function() use ($app) {
        $query = array();
        // Page setup
        $page = $app['request']->get('page');
        $page = $page ? $page : 1;
        $twigvars = array();
        $twigvars['sortby'] = $app['request']->get('sortby') ? $app['request']->get('sortby') : 'all';
        // Query Construction
        switch ($twigvars['sortby'])
        {
            case 'pending':
                $query['where_clause'] = " WHERE approved = 0";
                $twigvars['subtitle'] = "Pending Places";
                break;
            case 'rejected':
                $query['where_clause'] = " WHERE approved IS NULL";
                $twigvars['subtitle'] = "Rejected Places";
                break;
            case 'approved':
                $query['where_clause'] = " WHERE approved = 1";
                $twigvars['subtitle'] = "Approved Places";
                break;
            case 'search':
                $query['where_clause'] = " WHERE title LIKE '%" . $app->escape($app['request']->get('q')) . "%'";
                $twigvars['subtitle'] = "Search Results";
                break;
            default;
                $query['where_clause'] = "";
                $twigvars['subtitle'] = "All Results";
                break;
        }
        $offset = (10 * ($page - 1));
        $query['limit_clause'] = " LIMIT {$offset},10";
        $app['query'] = $query;
        
        // Pagination
        $page = $app['request']->get('page');
        $sql = "SELECT COUNT(*) As `count` FROM places".$query['where_clause'];
        $total = $app['db']->fetchAssoc($sql);
        $twigvars['totalpages'] = ceil($total['count'] / 10);
        $twigvars['startpage'] = 1;
        $twigvars['endpage'] = $twigvars['totalpages'];
        $twigvars['page'] = $page ? $page : 1;

        if ($twigvars['page'] >= 5) {
            $twigvars['startpage'] = $page - 8;
        }
        if ($twigvars['page'] <= ($twigvars['totalpages'] - 4)) {
            $twigvars['endpage'] = $twigvars['startpage'] + 8;
            if ($twigvars['endpage'] > $twigvars['totalpages']) {
                $twigvars['endpage'] = $twigvars['totalpages'];
            }
        }
        // End Pagination
        
        // Count places for navigation bar
        $sql = "SELECT approved, COUNT(*) AS `count` FROM places GROUP BY approved";
        $counts = $app['db']->fetchAll($sql);
        $twigvars['counts']['total'] = 0;
        $twigvars['counts']['rejected'] = 0;
        $twigvars['counts']['pending'] = 0;
        $twigvars['counts']['approved'] = 0;
        foreach ($counts as $count) {
            switch ($count['approved'])
            {
                case null:
                    $twigvars['counts']['rejected'] = $count['count'];
                    break;
                case 0:
                    $twigvars['counts']['pending'] = $count['count'];
                    break;
                case 1:
                    $twigvars['counts']['approved'] = $count['count'];
                    break;
            }
            $twigvars['counts']['total'] +=$count['count'];
        }
        $sortby = $app['request']->get('sortby');
        if (isset($twigvars['counts'][$sortby])) {
            $twigvars['counts']['total'] = $twigvars['counts'][$sortby];
        }
        
        // Shove in the twigvars
        $app['twigvars'] = $twigvars;
    };


/**
 * Admin 
 */
$app->get('/admin/', function() use($app) {
            // General variables
            $twigvars = $app['twigvars'];
            $twigvars['title'] = "Admin";
            // Fetch places from database
            $sql = "SELECT * FROM places" . $app['query']['where_clause'] . $app['query']['limit_clause'];
            $places = $app['db']->fetchAll($sql);
            if (count($places)) {
                $twigvars['tabledata'] = $places;
            }
            
            // RENDER!
            return $app['twig']->render('admin/index.twig', $twigvars);
        })->before($fetchPlaces);

$app->get('/admin/', function() use ($app) {
        return $app->redirect('/admin/all/');
    });

$app->get('/admin/edit/{id}', function() use($app) {
        $twigvars = $app['twigvars'];
        $sql = "SELECT * FROM places WHERE id = " . $app->escape($app['request']->get('id'));
        $twigvars['place'] = $app['db']->fetchAssoc($sql);
        return $app['twig']->render('admin/edit.twig', $twigvars);
    })->before($fetchPlaces);

$app->post('/admin/edit/', function() use($app) {
        $params = array();
        $params[] = $app->escape($app['request']->get('title'));
        $params[] = $app->escape($app['request']->get('type'));
        $params[] = $app->escape($app['request']->get('address'));
        $params[] = $app->escape($app['request']->get('uri'));
        $params[] = $app->escape($app['request']->get('description'));
        $params[] = $app->escape($app['request']->get('owner_name'));
        $params[] = $app->escape($app['request']->get('owner_email'));
        $params[] = $app->escape($app['request']->get('id'));

        $sql = "UPDATE places SET title = ?, type = ?, address = ?, uri = ?, description = ?, owner_name = ?, owner_email = ? WHERE id = ?";
        $stmt = $app['db']->prepare($sql);
        $details = $stmt->execute($params);
        return $app->redirect('/admin');
    });