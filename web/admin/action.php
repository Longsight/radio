<?php

error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

require '../../vendor/autoload.php';
require_once("../sonos.class.php");

Dotenv::load(__DIR__);

$ip = '10.2.2.243';
$spotifyUser = getenv('SPOTIFY_USER');
$masterPlaylist = getenv('MASTER_PLAYLIST');
$msg = '';

$sonos = new SonosPHPController($ip);

switch($_REQUEST['action']){

	case 'play':

		$sonos->Play();
		$msg = 'Play Requested to Sonos.';

		break;
	case 'pause':

		$sonos->Pause();

		$msg = 'Pause Requested to Sonos.';

		break;
	case 'stop':

		$sonos->Stop();

		$msg = 'Stop Requested to Sonos.';

		break;
	case 'start':

		$msg = 'New playlist added to Sonos and play requested.';

		$sonos->Stop();
		$sonos->RemoveAllTracksFromQueue();
		$sonos->AddSpotifyPlaylistToQueue($spotifyUser, $masterPlaylist);
		$sonos->Play();

		break;

}

print_r($msg);
die;
