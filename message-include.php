<?php

/*
 * This file is a part of the Lorhondel project.
 *
 * Copyright (c) 2021-present Valithor Obsidion <valzargaming@gmail.com>
 */
 
namespace Lorhondel;

if (is_null($message) || empty($message)) return; //An invalid message object was passed
if (is_null($message->content)) return; //Don't process messages without content
if ($message["webhook_id"]) return; //Don't process webhooks

$message_content = $message->content;
$message_id = $message->id;
$message_content_lower = strtolower($message_content);
if (!str_starts_with($message_content_lower, $lorhondel->command_symbol)) return;
$message_content = substr($message_content, strlen($lorhondel->command_symbol));
$message_content_lower = substr($message_content_lower, strlen($lorhondel->command_symbol));

/*
*********************
*********************
Load author data from message
*********************
*********************
*/

$author	= $message->author; //Member OR User object
if (get_class($author) == "Discord\Parts\User\Member") {
	$author_user = $author->user;
	$author_member = $author;
} else {
	$author_user = $author;
	$author_member = null;
	return; //Probably a bot or webhook without a member object
}

$user_createdTimestamp										= $author_user->createdTimestamp();
$author_channel 											= $message->channel;
$author_channel_id											= $author_channel->id;
$author_channel_class										= get_class($author_channel);
$is_dm = false;
if (is_null($message->channel->guild_id)) {
	return; //We shouldn't be processing direct messages right now
	$is_dm = true;
}

$author_username 											= $author_user->username;
$author_discriminator 										= $author_user->discriminator;
$author_id 													= $author_user->id;
$author_avatar 												= $author_user->avatar;
$author_check 												= $author_username.'#'.$author_discriminator;

$author_guild 												= $message->channel->guild;
$author_guild_id 											= $author_guild->id;
$author_guild_avatar 										= $author_guild->icon;
$author_guild_roles 										= $author_guild->roles;
$author_channel 											= $message->channel;
$author_channel_id											= $author_channel->id;

$author_member_roles = $author_member->roles;

/*
*********************
*********************
Process Lorhondel-related messages
*********************
*********************
*/
if ($author_guild_id != '887118559833112638') return;

$creator = false;
if ($author_id == 116927250145869826) $creator = true;

if(! $lorhondel->server) return;
if ($creator) { //Debug commands
	switch($message_content_lower) {
		case 'ping':
			return $message->reply('Pong!');
			break;
		case 'factory':
			$snowflake = \Lorhondel\generateSnowflake($lorhondel, time(), 0, 0, count($lorhondel->players));
			$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
				'id' => $snowflake,
				'user_id' => 116927250145869826,
				'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
				'health' => 0,
				'attack' => 1,
				'defense' => 2,
				'speed' => 3,
				'skillpoints' => 4,
			]);
			$message->reply($part);
			break;
		case 'characters':
			$characters = $lorhondel->players->filter(fn($p) => $p->user_id == $message->author->user->id);
			return $message->reply(json_encode($characters));
			break;
		case 'players':
			return $message->reply(json_encode($lorhondel->players));
			break;
		case 'parties':
			return $message->reply(json_encode($lorhondel->parties));
			break;
		case 'getcurrentplayer':
			$player = getCurrentPlayer($lorhondel, $author_id);
			if ($player) return $message->channel->sendEmbed(playerEmbed($lorhondel, getCurrentPlayer($lorhondel, $author_id)));
			else return $message->reply('No players found!');
			break;
		case 'getcurrentparty':
			if (! $player = getCurrentPlayer($lorhondel, $author_id))
				return $message->reply('No players found!');
			if (! $party = getCurrentParty($lorhondel, $player->id))
				return $message->reply('No party found!');
			return $message->channel->sendEmbed(partyEmbed($lorhondel, $party));
			break;
		case str_contains($message_content_lower, 'setcurrentplayer '):
			$id = trim(str_replace('setcurrentplayer ', '', $message_content_lower));
			if (is_numeric($id) && $player = $lorhondel->players->offsetGet($id))
				return $message->reply('Result: ' . (setCurrentPlayer($lorhondel, $author_id, $id) ?? 'None'));
			else return $message->reply('Invalid input! Numeric ID expected.');
			break;
		case 'playersfreshen':
			return $lorhondel->players->freshen();
			break;
		case 'part':
			$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
				'id' => 116927250145869826,
				'user_id' => 116927250145869826,
				'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
				'health' => 888,
				'attack' => 888,
				'defense' => 888,
				'speed' => 888,
				'skillpoints' => 888,
			]);
			$return = sqlGet(['*'], 'players', 'id', [$part->id], '', 1);
			echo '[RETURN]'; var_dump($return);
			foreach ($return as $data) {
				$part = $lorhondel->factory('\Lorhondel\Parts\Player\Player', $data);
				echo '[PART]';  var_dump($part);
			}
			return;
			break;
		case 'get':
			$url = Lorhondel\Http::BASE_URL . '/players/get/116927250145869826/';
			return $browser->post($url, ['Content-Type' => 'application/json'], json_encode('116927250145869826'))->then(
				function ($response) use ($lorhondel) {
					echo '[RESPONSE]' . PHP_EOL;
					print_r($response->getBody());
				},
				function ($error) {
					var_dump($error);
				}
			);
			break;
		case 'patch':
			$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
				'id' => 116927250145869826,
				'user_id' => 116927250145869826,
				'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
				'health' => 888,
				'attack' => 888,
				'defense' => 888,
				'speed' => 888,
				'skillpoints' => 888,
			]);
			echo '[PART]' . var_dump($part);
			return $lorhondel->players->save($part);
			break;
		case 'post':
			echo '[POST]' . PHP_EOL;
			$snowflake = \Lorhondel\generateSnowflake($lorhondel, time(), 0, 0, count($lorhondel->players));
			$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
				'id' => $snowflake,
				'user_id' => 116927250145869826,
				'party_id' => null,
				'active' => false,
				'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
				'health' => 0,
				'attack' => 1,
				'defense' => 2,
				'speed' => 3,
				'skillpoints' => 4,
			]);
			return $lorhondel->players->save($part);
			break;
		case 'save':
			echo '[SAVE]' . PHP_EOL;
			$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
				'id' => 116927250145869826,
				'user_id' => 116927250145869826,
				'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
				'health' => 0,
				'attack' => 1,
				'defense' => 2,
				'speed' => 3,
				'skillpoints' => 4,
			]);
			return $lorhondel->players->save($part);
			break;
		case 'delete':
			echo '[delete]' . PHP_EOL;
			$part = $lorhondel->factory(\Lorhondel\Parts\Player\Player::class, [
				'id' => 116927250145869826,
				'user_id' => 116927250145869826,
				'species' => 'Elarian', //Elarian, Manthean, Noldarus, Veias, Jedoa
				'health' => 0,
				'attack' => 1,
				'defense' => 2,
				'speed' => 3,
				'skillpoints' => 4,
			]);
			return $lorhondel->players->delete($part);
			break;
		case 'stats':
			if ($embed = $stats->handle())
				return $message->channel->sendEmbed($embed);
			break;
		case 'discord':
			return $message->reply(get_class($lorhondel->discord));
			break;
		case '__create party':
			if (count($collection = $lorhondel->players->filter(fn($p) => $p->user_id == $author_id && $p->active == 1 ))>0) {
				echo '[COLLECTION PARTY]'; var_dump($collection);
				foreach ($collection as $player) {
					if (property_exists($player, 'timer')) return $message->reply('Please wait for the previous task to finish!');
					if ($player instanceof \Lorhondel\Parts\Player\Player) {
						if (! $player->party_id) {
							$lorhondel->parties->save($lorhondel->factory(\Lorhondel\Parts\Party\Party::class, ['player1' => $player->id]));
							$lorhondel->parties->freshen();
							$message->react("⏰");
							$timer = $lorhondel->getLoop()->addTimer(3, 
								function($result) use ($lorhondel, $message, $player) {
									unset($player->timer);
									$message->react("👍");
									if (! empty($collection = $lorhondel->parties->filter(fn($p) => $p->player1 == $player->id))) {
										echo '[COLLECTION]'; var_dump($collection);
										foreach ($collection as $party) { //There should only be one
											$player->party_id = $party->id;
											echo '[COLLECTION PLAYER]'; var_dump($player);
											$lorhondel->players->save($player);
											return $message->reply('Party created and assigned!');
										}
									} else return $message->reply('Unable to locate party part!');
								}
							);
							$player->timer = $timer;
							return;
						} else return $message->reply('You must leave your current party first!');
					} else return $message->reply('No players found!');
				}
			} else return $message->reply('No active players found!');
			break;
		default:
			break;
	}
}

echo '[LORHONDEL MESSAGE] ' . $author_id . PHP_EOL;
if ($player = getCurrentPlayer($lorhondel, $author_id))
	$party = getCurrentParty($lorhondel, $player->id);

if (str_starts_with($message_content_lower, 'help')) {
	$documentation = '';
}


if (str_starts_with($message_content_lower, 'player')) {
	$name = $message_content = trim(substr($message_content, 6)); echo "[NAME] $name" . PHP_EOL;
	$id = $message_content_lower = trim(substr($message_content_lower, 6)); echo "[ID] $id" . PHP_EOL;
	foreach (['<@', '!', '>'] as $filter) {
		$name = $message_content = str_replace($filter, '', $name);
		$id = $message_content_lower = str_replace($filter, '', $id);
	}
	/*
	*********************
	*********************
	Player Repository commands
	These commands take a Discord ID to check for Player permissions.
	NOTE: Permission is assumed to be allowed if the relevant data is not passed!
	*********************
	*********************
	*/	
	$player_repository_commands = ['new', 'activate'];
	foreach($player_repository_commands as $command) {
		if (str_starts_with($message_content_lower, $command)) {
			echo "[PLAYER REPOSITORY REFLECTION COMMAND] $command" . PHP_EOL;
			$message_content = trim(substr($message_content, strlen($command)));
			$tokens = array_merge([$author_id], explode(' ', $message_content));
			$reflection = new \ReflectionMethod('\Lorhondel\Repository\PlayerRepository', $command);
			$num = $reflection->getNumberOfParameters();
			$tokens = array_slice($tokens, 0, $num);
			//if (count($tokens) < $num) return $message->reply('Invalid number of parameters!'); //Add class function that returns a string
			return $message->reply(call_user_func_array(array($lorhondel->players, $command), $tokens));
		}
	}
	if (is_numeric($id)) {
		if ($target_player = $lorhondel->players->offsetGet($id) ?? getCurrentPlayer($lorhondel, $id))
			return $message->channel->sendEmbed(playerEmbed($lorhondel, $target_player));
		else return $message->reply("Unable to locate either a Player or Discord account with a Player for ID `$id`!");
	}
	/*
	*********************
	*********************
	Player Part commands
	These commands require an active player
	*********************
	*********************
	*/
	if (! $player) return $message->reply('Please create a Player or activate one first!');
	$player_commands = ['deactivate', 'rename', 'looking'];
	foreach($player_commands as $command) {
		if (str_starts_with($message_content_lower, $command)) {
			echo "[PLAYER PART REFLECTION COMMAND] $command" . PHP_EOL;
			$message_content = trim(substr($message_content, strlen($command)));
			$tokens = array_merge([$lorhondel], explode(' ', $message_content));
			$reflection = new \ReflectionMethod('\Lorhondel\Parts\Player\Player', $command);
			$num = $reflection->getNumberOfParameters();
			$tokens = array_slice($tokens, 0, $num);
			if (count($tokens) < $num) return $message->reply('Invalid number of parameters!'); //Add class function that returns a string
			return $message->reply(call_user_func_array(array($player, $command), $tokens));
		}
	}
	if (! $message_content_lower) {
		return $message->channel->sendEmbed(playerEmbed($lorhondel, $player));
	}
	elseif ($message_content_lower) {
		$help = '';
		if (! str_starts_with($message_content_lower, 'help')) $help .= "Unrecognized Party subcommand `$message_content_lower`. ";
		$help .= 'Current available Player subcommands are: ';
		foreach ($player_repository_commands as $command) {
			$help .= "`$command`, ";
		}
		foreach ($player_commands as $command) {
			$help .= "`$command`, ";
		}
		$help = substr($help, 0, strlen($help)-2) . '.';
		return $message->reply($help);
	}
}

if (str_starts_with($message_content_lower, 'party')) {
	$name = $message_content = trim(substr($message_content, 5));
	$id = $message_content_lower = trim(substr($message_content_lower, 5));
	foreach (['<@', '!', '>'] as $filter) {
		$name = str_replace($filter, '', $name);
		$id = str_replace($filter, '', $id);
	}
	
	if (is_numeric($id)) {
		if ($target_party = $lorhondel->parties->offsetGet($id))
			return $message->channel->sendEmbed(partyEmbed($lorhondel, $target_party));
		elseif ($target_player = getCurrentPlayer($lorhondel, $id) ?? $lorhondel->players->offsetGet($id)) {
			if ($target_party = getCurrentParty($lorhondel, $target_player->id)) {
				return $message->channel->sendEmbed(partyEmbed($lorhondel, $target_party));
			} else return $message->reply("Unable to locate a party for Player with ID `$id`!");
		} else return $message->reply("Unable to locate a party with ID `$id`!");
	}/* else { //Search by name (Unreliable, not unique, and conflicts with commands. Best not to use this..)
		if (count($collection = $lorhondel->parties->filter(fn($p) => $p->name == $name))== 1) {
			foreach ($collection as $party) 
			if ($target_party = $lorhondel->parties->offsetGet($id))
				return $message->channel->sendEmbed(partyEmbed($lorhondel, $target_party));
			elseif ($target_player = getCurrentPlayer($lorhondel, $id) ?? $lorhondel->players->offsetGet($id)) {
				if ($target_party = getCurrentParty($lorhondel, $target_player->id)) {
					return $message->channel->sendEmbed(partyEmbed($lorhondel, $target_party));
				} else return $message->reply("Unable to locate a party for Player with ID `$id`!");
			} else return $message->reply("Unable to locate a party with ID `$id`!");
		} elseif (count($collection = $lorhondel->parties->filter(fn($p) => $p->name == $name))> 1) {
			return $message->reply("There is more than one party sharing the name `$name`! Please change the name of your party, or ask its creator.");
		} elseif (count($collection = $lorhondel->parties->filter(fn($p) => $p->name == $name))== 0) {
			return $message->reply("Unable to locate a party with name `$name`!");
		}
	}*/
	
	/*
	*********************
	*********************
	Party Repository Commands
	These commands take both a Player and Party to check for permissions.
	NOTE: Permission is assumed to be allowed if the relevant data is not passed!
	*********************
	*********************
	*/
	if (! $player) return $message->reply('Please create a Player or activate one first!');
	$party_repository_commands = ['new', 'join'];
	foreach($party_repository_commands as $command) {
		if (str_starts_with($message_content_lower, $command)) {
			echo "[PARTY REPOSITORY REFLECTION COMMAND] $command" . PHP_EOL;
			$message_content = trim(substr($message_content, strlen($command)));
			$tokens = array_merge([$player, $party], explode(' ', $message_content));
			$reflection = new \ReflectionMethod('\Lorhondel\Repository\PartyRepository', $command);
			$num = $reflection->getNumberOfParameters();
			$tokens = array_slice($tokens, 0, $num);
			//if (count($tokens) < $num) return $message->reply('Invalid number of parameters!'); //Add class function that returns a string
			return $message->reply(call_user_func_array(array($lorhondel->parties, $command), $tokens));
		}
	}
	/*
	*********************
	*********************
	Party Part Commands
	These commands require an active Party and Player to check for permissions.
	NOTE: Permission is assumed to be allowed if the relevant data is not passed!
	*********************
	*********************
	*/
	if (! $party) return $message->reply('No active party found! Try creating one with `;party create {party name here}` or joining one with `;party join {party or Player id here}`');
	$party_commands = ['leave', 'disband', 'invite', 'uninvite', 'rename', 'looking'];
	foreach($party_commands as $command) {
		if (str_starts_with($message_content_lower, $command)) {
			echo "[PARTY PART REFLECTION COMMAND] $command" . PHP_EOL;
			$message_content = trim(substr($message_content, strlen($command)));
			$tokens = array_merge([$lorhondel, $player], explode(' ', $message_content));
			$reflection = new \ReflectionMethod('\Lorhondel\Parts\Party\Party', $command);
			$num = $reflection->getNumberOfParameters();
			$tokens = array_slice($tokens, 0, $num);
			if (count($tokens) < $num) return $message->reply('Invalid number of parameters!'); //Add class function that returns a string
			return $message->reply(call_user_func_array(array($party, $command), $tokens));
		}
	}
	if (! $message_content_lower) {
		return $message->channel->sendEmbed(partyEmbed($lorhondel, $party));
	}
	elseif ($message_content_lower) {
		$help = '';
		if (! str_starts_with($message_content_lower, 'help')) $help .= "Unrecognized Party subcommand `$message_content_lower`. ";
		$help .= 'Current available Party subcommands are: ';
		foreach ($party_repository_commands as $command) {
			$help .= "`$command`, ";
		}
		foreach ($party_commands as $command) {
			$help .= "`$command`, ";
		}
		$help = substr($help, 0, strlen($help)-2) . '.';
		return $message->reply($help);
	}
}