<?php

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

require '../../vendor/autoload.php';
require_once("../sonos.class.php");

Dotenv::load(__DIR__);

$ip = '10.2.8.98';
$spotifyUser = getenv('SPOTIFY_USER');
$masterPlaylist = getenv('MASTER_PLAYLIST');
$msg = '';

$sonos = new SonosPHPController($ip);

$sonos->Stop();
$sonos->RemoveAllTracksFromQueue();
$sonos->AddSpotifyPlaylistToQueue($spotifyUser, $masterPlaylist);
$sonos->Play();
