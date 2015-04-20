<?php
class Elo {
	/**
	 * Update the two passed Elo-scores by reference.
	 * From this method, an arbitrary function can be called to calculate the new score.
	 * @param int $player1 score of player 1 from before the game
	 * @param int $player2 score of player 2 from before the game
	 * @param int $winner determines who the winner was. 1 -> player 1 won, 2 -> player 2 won, anything else -> stalemate
	 */
	public static function updateElo(&$player1, &$player2, $winner) {
		if(!is_numeric($player1) || !is_numeric($player2)) {
			throw new Exception("Passed Elo-Numbers ($player1, $player2) are not numeric.");
		}
		$factor1 = 0.5;
		$factor2 = 0.5;
		if($winner === 1) {
			$factor1 = 1;
			$factor2 = 0;
		} elseif($winner === 2) {
			$factor1 = 0;
			$factor2 = 1;
		}
		$tmp = $player1;
		$player1 = Elo::calculateElo($player1, $player2, $factor1);
		$player2 = Elo::calculateElo($player2, $tmp, $factor2);
	}

	/**
	 * Calculates the new Elo-score for one player based on the formula taken from Formula is taken from: <a href="http://de.wikipedia.org/wiki/Elo-Zahl#Berechnung">The german wiki</a>.
	 *
	 * @param int $elo1 Elo-score of player 1 from before the game (this is the player the new score is calculated for)
	 * @param int $elo2 Elo-score of player 2 from before the game
	 * @param float $points points to assign (should be like this: 0 -> p1 lost, 0.5 -> stalemate, 1 -> p1 won
	 * @return number new Elo-score for player 1
	 */
	private static function calculateElo($elo1, $elo2, $points) {
		$a = 400;
		$exp = round((max(min($elo2 - $elo1, $a), -$a))/$a,1);
		$e = round(1/(1+pow(10,$exp)),3);
		$elo = $elo1 + 10 * ($points - $e);
		return round($elo);
	}
}
?>