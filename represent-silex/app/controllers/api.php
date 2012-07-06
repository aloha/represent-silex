<?php

/**
 * Add place 
 */
$app->post("/places", function() use($app) {
        $owner_name = $app->escape($_POST['owner_name']);
        $owner_email = $app->escape($_POST['owner_email']);
        $title = $app->escape($_POST['title']);
        $type = $app->escape($_POST['type']);
        $address = $app->escape($_POST['address']);
        $uri = $app->escape($_POST['uri']);
        $description = $app->escape($_POST['description']);

        // validate fields
        if (empty($title)
            || empty($type) || empty($address)
            || empty($uri) || empty($description)
            || empty($owner_name) || empty($owner_email)) {
            return new Response('All fields are required - please try again.', 201);
        } else {
            try {
                $sql = "INSERT INTO places (approved, title, type, address, uri, description, owner_name, owner_email) ";
                $sql .= "VALUES ('0', '$title', '$type', '$address', '$uri', '$description', '$owner_name', '$owner_email')";
                $places = $app['db']->exec($sql);
            } catch (Exception $e) {
                return new Response('Ho Shucks! Someting Broke! If it happens again emails us and let us know!', 201);
            }
        }
        return new Response('success', 201);
    });

$app->get('/admin/approve/{id}', function() use($app) {
        try {
            $sql = "UPDATE places SET approved = 1 WHERE id = " . $app->escape($app['request']->get('id'));
            $places = $app['db']->exec($sql);
        } catch (Exception $e) {
            return new Response('Ho Shucks! Someting Broke!', 201);
        }
        return $app->redirect('/admin');
    });

$app->get('/admin/reject/{id}', function() use($app) {
        try {
            $sql = "UPDATE places SET approved = NULL WHERE id = " . $app->escape($app['request']->get('id'));
            $places = $app['db']->exec($sql);
        } catch (Exception $e) {
            return new Response('Ho Shucks! Someting Broke!', 201);
        }
        return $app->redirect('/admin');
    });

$app->get('/admin/geocode/', function() use($app) {
        $sql = "SELECT * FROM places WHERE lat=0 OR lng=0";
        $base_url = "http://maps.google.com/maps/geo?output=xml" . "&key=" . $app['config']['googleapi_key'];
        $delay = 0;
        $places = $app['db']->fetchAll($sql);
        foreach ($places as $place) {
            $geocode_pending = true;
            while ($geocode_pending)
            {
                $request_url = $base_url . "&q=" . urlencode($place["address"]);
                $xml = simplexml_load_file($request_url) or die("url not loading");
                $status = $xml->Response->Status->code;
                $status = "200";
                if ($status == "200") {
                    // Successful geocode
                    $geocode_pending = false;
                    $coords = explode(",", $xml->Response->Placemark->Point->coordinates);
                    // Format: Longitude, Latitude, Altitude
                    $lat = $app->escape($coords[1]);
                    $lng = $app->escape($coords[0]);
                    $sql = "UPDATE places SET lat = ?, lng = ? WHERE id = ? LIMIT 1;";
                    $stmt = $app['db']->prepare($sql);
                    $stmt->execute(array($lat, $lng, $app->escape($place['id'])));
                } else if ($status == "620") {
                    // sent geocodes too fast
                    $delay += 100000;
                } else {
                    // failure to geocode
                    $geocode_pending = false;
                }
                usleep($delay);
            }
        }
        return $app->redirect('/admin');
    });