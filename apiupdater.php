<?php

    /**
     * Freifunk API Nodecount Updater
     *
     * Requirement: Existing API JSON File (*https://freifunk.net/api-generator/)(
     * Replace state->nodes with "nodes": !!NODES!!
     * Replace state->lastchange with "@lastchange": "!!LASTCHANGE!!",
     * Enter path for input and output below
     * Run as Cron/Timer
     *
     * Note: Date format for LASTCHANGE may not be valid - is there any
     *       standard what to use inside the API file?!
     *
     * @author Florian Knodt <freifunk@adlerweb.info>
     */

    $nodesjson = 'http://map.freifunk-myk.de/data/nodes.json';
    $input = '/var/www/api.json.template';
    $output = '/var/www/api.json';

    //End of config

    $data = file_get_contents($nodesjson);
    $data = json_decode($data) || trigger_error('Could not parse nodes JSON', E_USER_ERROR);

    //Count via foreach() instead of count() to ignore gateways and offline nodes
    $nodes = 0;
    foreach($data->nodes as $node) {
            if($node->flags->online && !$node->flags->gateway) $nodes++;
    }

    if(!file_exists($input) || !is_readable($input)) {
        trigger_error('Could not read input JSON', E_USER_ERROR);
    }

    if(!is_writable($output)) {
        trigger_error('Could not write output JSON', E_USER_ERROR);
    }

    $data = file_get_contents($input);
    $data = str_replace('!!NODES!!', $nodes, $data);
    $data = str_replace('!!LASTCHANGE!!', strftime('%Y-%m-%dT%T.000%Z'), $data);
    file_put_contents($output, $data);

?>
