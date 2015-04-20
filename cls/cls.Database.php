<?php
require_once('conf.php');
require_once('inc/inc.func.php');
require_once('inc/inc.const.php');
require_once('cls/cls.Elo.php');

// queries in this file have a non-indented ending SQL;. That's to avoid a bug with notepad++, no mistake or functional reason
class Database {
	private static $instance;
	
	/**
	 * @return PDO singleton-instance
	 */
	public static function getInstance() {
		if(!isset($instance)) {
			self::$instance = new PDO('mysql:host=localhost;dbname='.DB_DATABASE.';charset=utf8', DB_USER, DB_PW);
			self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		return self::$instance;
	}
	
	private function Database(){}
	
	/**
	 * Gets ALL players with their Elo-number for a specific game
	 * @param int $gameId the id of the game
	 * @return array: associative array with fields [playerid, firstname, lastname, nickname, image, gameid, elo]
	 * @throws Exception if the passed gameid is invalid
	 */
	public static function getPlayersForGame($gameId) {
		if(!isValidId($gameId)) {
			throw new Exception("Passed gameid ($gameId) is invalid.");
		}
		$sql = <<<SQL
			SELECT 
				`player`.`playerid`,
				`player`.`firstname`,
				`player`.`lastname`,
				`player`.`nickname`,
				`player`.`image`,
				IFNULL(`elo`.`gameid`, :gameid) AS `gameid`,
				IFNULL(`elo`.`elo`, :defaultelo) AS `elo`
			FROM player
			LEFT JOIN `elo` ON `player`.`playerid` = `elo`.`playerid`
			AND `elo`.`gameid` = :gameid
			ORDER BY `player`.`lastname`, `player`.`firstname`;
SQL;
		$stmt = Database::getInstance()->prepare($sql);
		$stmt->bindValue(':gameid', $gameId);
		$stmt->bindValue(':defaultelo', Constants::ELO_DEFAULT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Fetches all games
	 * @return array: associative array of games [gameid, gameacronyme, gamename]
	 */
	public static function getGames() {
		$sql = <<<SQL
		SELECT `game`.gameid, `game`.acronyme AS `gameacronyme`, `game`.name AS `gamename` 
				FROM `game`;
SQL;
		return Database::getInstance()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Inserts a new player into the DB, if it doesn't already exist
	 * @param string $firstname first name
	 * @param string $lastname last name
	 * @param string $nickname nickname
	 * @throws Exception if the parameters are null or the player already exists (that is a player with firstname, lastname and nickname as given)
	 */
	public static function createPlayer($firstname, $lastname, $nickname) {
		if(is_null($firstname) || is_null($lastname) || is_null($nickname)) {
			throw new Exception("Firstname, lastname and nickname must not be null.");
		}
		$sql = <<<SQL
			SELECT `player`.`playerid` 
			FROM `player`
			WHERE `player`.`firstname` = ? AND `player`.`lastname` = ? AND `player`.`nickname` = ?;
SQL;
		$stmt = Database::getInstance()->prepare($sql);
		$stmt->execute(array($firstname, $lastname, $nickname));
		if(count($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0) {
			throw new Exception("Player ($firstname '$nickname' $lastname) already exists. Nothing was inserted.");
		}	
		$sql = <<<SQL
			INSERT INTO `player`(`player`.`firstname`, `player`.`lastname`, `player`.`nickname`)
				VALUES (?, ?, ?);
SQL;
		$stmt = Database::getInstance()->prepare($sql);
		$stmt->execute(array($firstname, $lastname, $nickname));
	}
	
	/**
	 * Fetches all available Elo-numbers for one player
	 * @param int $playerId id of the player
	 * @return array: associative array of the form [gameid -> elo for that game]
	 */
	public static function getPlayerElos($playerId) {
		$sql = <<<SQL
			SELECT `gameid`, `elo`
				FROM `elo`
				WHERE `playerid` = :playerid;
SQL;
		$stmt = Database::getInstance()->prepare($sql);
		$stmt->bindValue(':playerid', $playerId);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$elos = array();
		foreach($result as $r) {
			$elos[$r['gameid']] = $r['elo'];
		}
		return $elos;
	}
	
	/**
	 * Gets the stats for a player
	 * @param int $playerId id of the player
	 * @return array: associative array of the form
	 * gameid -> 
	 * 	[
	 * 		'elo' -> int, Elo-number for this game
	 * 		'wins' -> int, number of wins in this game
	 *		'defeats' -> int, number of lost matches in this game
	 *		'draws' -> int, number of draws in this game 
	 *		'characters' -> array
	 *			[
	 *				characterid -> int, number of times this character was played
	 *				...
	 *			]
	 * ]
	 * 
	 * @throws BadMethodCallException
	 */
	public static function getPlayerStats($playerId) {
		throw new BadMethodCallException("Not implemted yet");
		$sql = <<<SQL
			SELECT 	
SQL;
	}
	
	/**
	 * Fetches a player from the database
	 * @param int $playerid id to determine the player by 
	 * @return array: associative array with the fields for keys
	 * [
	 * 	'playerid' -> int
	 *  'firstname' -> string
	 *  'lastname' -> string
	 *  'nickname' -> string
	 *  'eloavg' -> int 
	 *  'elos' -> array 
	 *  	[
	 *  		gameid -> int (elo for that game)
	 *  		...
	 *  	]
	 * ]
	 */
	public static function getPlayer($playerId) {
		$sql = <<<SQL
			SELECT 
					`player`.`playerid`,
					`player`.`firstname`,
					`player`.`lastname`,
					`player`.`nickname`,
					ROUND((SELECT AVG(`elo`.`elo`) FROM `elo` WHERE `elo`.`playerid` = :playerid)) AS `eloavg`
				FROM `player`
				WHERE `player`.`playerid` = :playerid;
SQL;
		$stmt = Database::getInstance()->prepare($sql);
		$stmt->bindValue(':playerid', $playerId);
		$stmt->execute();
		$player = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
		$player['elos'] = Database::getPlayerElos($playerId);
		return $player;
	}
	
	/**
	 * Fetches all characters for a given game, specified by it's id
	 * @param int $gameid id of the game
	 * @return array: associative array of characters for the given game [characterid, charactername]. Empty array if id was invalid
	 */
	public static function getCharacters($gameid) {
		$sql = <<<SQL
		SELECT `character`.characterid as characterid, `character`.name as charactername
				FROM  `gamecharacter`
					INNER JOIN `game`
						ON `gamecharacter`.gameid = `game`.gameid
					INNER JOIN `character`
						ON `gamecharacter`.characterid = `character`.characterid
				WHERE (`game`.gameid = :id)
SQL;
		$dbh = self::getInstance();
		$stmt = $dbh->prepare($sql);
		$stmt->bindValue(':id', $gameid);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * Inserts one match into the database
	 * @param int $p1Id id of player one
	 * @param int $p2Id id of player two
	 * @param int $c1Id id of player one's character
	 * @param int $c2Id id of player two's character
	 * @param int $gameId id of the game this match for played in
	 * @param int $winnerId id of the winner of the match (null for a stalemate)
	 * @throws Exception if the passed ids are invalid
	 */
	public static function insertMatch($p1Id, $p2Id, $c1Id, $c2Id, $gameId, $winnerId) {
		if(!isValidId($p1Id) || !isValidId($p2Id) || !isValidId($c1Id) || !isValidId($c2Id)) {
			throw new Exception("Please pass valid IDs for player one, player two, character one and character two.");			
		}
		if($p1Id === $p2Id) {
			throw new Exception("Players can't play against themselves.");
		}
		if($winnerId !== null && $winnerId != $p1Id && $winnerId != $p2Id) {
			throw new Exception("Error while trying to inserting match. ID of winner ($winnerId) matches neither player one ($p1Id) nor player two ($p2Id).");
		}
		$sql = <<<SQL
		INSERT INTO `match`(`winnerid`,`gameid`)
			VALUES (:winnerid,:gameid);
		SET @last_match_id = LAST_INSERT_ID();
		INSERT INTO `playermatch`(`characterid`,`matchid`,`playerid`)
			VALUES
				(:characterOneId,@last_match_id,:playerOneId),
				(:characterTwoId,@last_match_id,:playerTwoId);
SQL;
		$dbh = self::getInstance();
		$stmt = $dbh->prepare($sql);
		$stmt->bindValue(':winnerid', $winnerId);
		$stmt->bindValue(':gameid', $gameId);
		$stmt->bindValue(':characterOneId', $c1Id);
		$stmt->bindValue(':characterTwoId', $c2Id);
		$stmt->bindValue(':playerOneId', $p1Id);
		$stmt->bindValue(':playerTwoId', $p2Id);
		$stmt->execute();
		
		// recalc the elo after this match and write them back to the db
		$p1Elo = @Database::getPlayer($p1Id)['elos'][$gameId];
		$p2Elo = @Database::getPlayer($p2Id)['elos'][$gameId];
		// the elo is null for games the player hasn't played yet -> init
		if($p1Elo === null) {
			$p1Elo = Constants::ELO_DEFAULT;
		}
		if($p2Elo === null) {
			$p2Elo = Constants::ELO_DEFAULT;
		}
				
		$winner = 0;
		if($p1Id === $winnerId) {
			$winner = 1;
		} elseif($p2Id === $winnerId) {
			$winner = 2;
		} 
		Elo::updateElo($p1Elo, $p2Elo, $winner);
		Database::updateElo($p1Id, $gameId, $p1Elo);
		Database::updateElo($p2Id, $gameId, $p2Elo);
	}
	
	/**
	 * Updates the Elo-number of a given player for a given game
	 * @param int $playerId id of the player to update
	 * @param int $gameId id of the game this Elo-number belongs to
	 * @param int $elo the new Elo-number
	 */
	private static function updateElo($playerId, $gameId, $elo) {
		$sql = <<<SQL
			INSERT INTO `elo`(`playerid`,`gameid`,`elo`) 
				VALUES (:playerid, :gameid, :elo)
			ON DUPLICATE KEY UPDATE
				`elo` = :eloup;
SQL;
		$dbh = Database::getInstance();
		$stmt = $dbh->prepare($sql);
		$stmt->bindValue(':elo', $elo);
		$stmt->bindValue(':eloup', $elo);
		$stmt->bindValue(':playerid', $playerId);
		$stmt->bindValue(':gameid', $gameId);
		$stmt->execute();
	}
	
	/**
	 * Recalculates the Elo-number for all players. This is especially useful if somebody manipulated the score by entering fake matches or when a match was revoked.
	 * This method will reset all Elo-numbers and go through all previous matches and re-apply the Elo-function for every single match. So keep in mind:<br>
	 * - This can be horribly slow, if thousands of matches have been played already! So use it sparely.<br>
	 * - Only matches that still exist are being put into consideration. So if player A won a lot against player B, and player B was deleted from the DB (and therefore all of his matches are cascade-deleted), A's Elo-score will be significantly lower. This can be desired or unwanted.<br>
	 */
	public static function recalcElo() {
		// remove all Elo-numbers. Elo-numbers with existing matches will have their value recalculated
		$default = Constants::ELO_DEFAULT;
		$sql = <<<SQL
			DELETE FROM `elo`;
SQL;
		$dbh = Database::getInstance();
		$dbh->exec($sql);
		
		// get all the matches played so far, where every match has exactly two consecutive entries in the result-set (one for each participating player)
		$sql = <<<SQL
			SELECT  `match`.gameid, `match`.`matchid` ,  `match`.`winnerid` , p.`playerid` 
			FROM  `match` 
			JOIN  `playermatch` p ON  `match`.`matchid` = p.matchid
			ORDER BY  `matchid` 
SQL;
		$matches = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		assert(count($matches)%2 === 0);
		
		// recalc the Elo-numbers for all matches store them associative [playerid -> elo]
		$elos = array();
		for($i = 0; $i < count($matches)-1; $i+=2) {
			$gameId = $matches[$i]['gameid'];
			$winnerId = $matches[$i]['winnerid'];
			$p1Id = $matches[$i]['playerid'];
			$p2Id = $matches[$i+1]['playerid'];
			
			// players don't have any elos yet
			if(!array_key_exists($p1Id,$elos)) {
				$elos[$p1Id] = array();
			}
			if(!array_key_exists($p2Id,$elos)) {
				$elos[$p2Id] = array();
			}
			// players don't have elos for this game
			if(!array_key_exists($gameId,$elos[$p1Id])) {
				$elos[$p1Id][$gameId] = $default;
			}
			if(!array_key_exists($gameId,$elos[$p2Id])) {
				$elos[$p2Id][$gameId] = $default;
			}
			
			$winner = 0;
			if($winnerId === $p1Id) {
				$winner = 1;
			} elseif($winnerId === $p2Id) {
				$winner = 2;
			}
			Elo::updateElo($elos[$p1Id][$gameId], $elos[$p2Id][$gameId], $winner);
		}
		
		// re-write the newly calculated Elo-numbers. This might also take a while, as we are issuing one query per player...
		$dbh->beginTransaction();
		foreach($elos as $playerid => $elo) {
			foreach($elo as $game => $e) {
				Database::updateElo($playerid, $game, $e);
			}
		}	
		$dbh->commit();
	}
}