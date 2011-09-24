<?
/**
 * This is a simple interface to the Wordnik API, which wraps API calls with
 * PHP methods, and returns arrays of standard php objects containing the results.
 *
 * These examples only show a few of the available API calls (for getting definitions,
 * examples, related words, Wordnik's word of the say, and random words). See the full list here:
 * http://docs.wordnik.com/api/methods
 *
 * To use the API you'll need a key, which you can apply for here:
 * http://api.wordnik.com/signup/
 *
 * After you receive your key assign it to the API_KEY constant, below.
 * Then, to get an array of definition objects, do something like this:
 *
 * require_once('WordnikModel.php');
 * $definitions = Wordnik::instance()->getDefinitions('donkey');
 *
 * $definitions will hold an array of objects, which can be accessed individually:
 * $definitions[0]->headword
 *
 * Or you can loop through the results and display info about each,
 * which could look something like this in a template context:
 *
 * <ul>
 * <? foreach ($definitions as $definition): ?>
 *     <li>
 *       <strong><?= $definition->headword ?></strong:
 *       <?= $definition->text ?>
 *     </li>
 * <? endforeach; ?>
 * </ul>
 *
 * Please send comments or questions to apiteam@wordnik.com.
 *
 */
class Wordnik {

	const API_KEY = "a28025b0aea6db45e39330372c7068a43e14e2ab367eea6fb";
	const BASE_URI = 'http://api.wordnik.com/api';

	/** Pass in a word as a string, get back an array of definitions. */
	public function getDefinitions($word) {
		if(is_null($word) || trim($word) == '') {
			throw new InvalidParameterException("getDefinitions expects word to be a string");
		}

		return $this->curlData( '/word.json/' . rawurlencode($word) . '/definitions' );
	}

	/** Pass in a word as a string, get back an array of related words. */
	public function getRelatedWords($word) {
		if(is_null($word) || trim($word) == '') {
			throw new InvalidParameterException("getRelatedWords expects word to be a string");
		}

		return $this->curlData( '/word.json/' . rawurlencode($word) . '/related' );
	}

	/** Pass in a word as a string, get back an array of example sentences. */
	public function getExamples($word) {
		if(is_null($word) || trim($word) == '') {
			throw new InvalidParameterException("getExamples expects word to be a string");
		}

		return $this->curlData( '/word.json/' . rawurlencode($word) . '/examples' );
	}

	/** Pass in a word as a string, get back the Word of the Day. */
	public function getWordOfTheDay() {
		return $this->curlData( '/wordoftheday.json/' );
	}

	/** Pass in a word as a string, get back a random word. */
	public function getRandomWord() {
		return $this->curlData( '/words.json/randomWord' );
	}

	/** Utility method to call json apis.
	  * This presumes you want JSON back; could be adapted for XML pretty easily. */
	private function curlData($uri) {
		$data = null;

		$header = array();
		$header[] = "Accept: application/json";
		$header[] = "api_key: " . self::API_KEY;

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_TIMEOUT, 5); // 5 second timeout
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // return the result on success, rather than just TRUE
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt ($curl, CURLOPT_URL, self::BASE_URI . $uri);

		$response = curl_exec($curl);
		$response_info = curl_getinfo($curl);

		if ($response_info['http_code'] == 0) {
			//throw new Exception( "TIMEOUT: curlData Api call to " . $uri . " took more than 5s to return" );
		} else if ($response_info['http_code'] == 200) {
			$data = json_decode($response);
		} else if ($response_info['http_code'] == 404) {
			$data = null;
		} else {
			throw new Exception("Can't connect to the api: " . $uri . " response code: " . $response_info['http_code']);
		}

		return $data;
	}

}