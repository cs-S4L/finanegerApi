<?php
namespace src\classes;

class Validate {

	private $regexes = array(
		'email' => "/.{2,}@.{2,}\..+/",
		'word' => "/^[a-zA-ZöÖüÜäÄ]+$/",
		'digits' => "/^\d+$/"
	);

	public static function get() {
        static $validatorObject = null;

        if (is_null($validatorObject)) $validatorObject = new self();

        return $validatorObject;
	}

	public function escapeStrings(&...$args) {
		foreach($args as $key => $value) {
			$args[$key] = htmlspecialchars(trim($value));
		}
	}

	public function validate($type, $value, &$errors, $errorKey) {
		switch ($type){
			case 'email':
				if (empty($value)) {
					$this->addError(
						$errors,
						$errorKey,
						'Leere E-Mailadresse'
					);
					return;
				}
				if (!$this->checkRegex($value, $this->regexes['email'])) {
					$this->addError(
						$errors,
						$errorKey,
						'Invalide E-Mailadresse'	
					);
				}
				break;
			case 'word':
				if (empty($value)) {
					$this->addError(
						$errors,
						$errorKey,
						'Leeres Feld'
					);
					return;
				}
				if (!$this->checkRegex($value, $this->regexes['word'])) {
					$this->addError(
						$errors,
						$errorKey,
						'Invalide Zeichen. Nur Buchstaben erlaubt'	
					);
				}
				break;
			case 'digit':
				if (empty($value)) {
					$this->addError(
						$errors,
						$errorKey,
						'Leeres Feld'
					);
					return;
				}
				if (!$this->checkRegex($value, $this->regexes['digit'])) {
					$this->addError(
						$errors,
						$errorKey,
						'Invalide Zeichen. Nur Zahlen erlaubt'	
					);
				}
				break;
			case 'password':
				if (empty($value)) {
					$this->addError(
						$errors,
						$errorKey,
						'Leeres Passwort'
					);
					return;
				}
				if (\strlen($value) < 8) {
					$this->addError(
						$errors,
						$errorKey,
						'Passwort zu kurz!'	
					);
				}
				break;
			default:
				if (empty($value)) {
					$this->addError(
						$errors,
						$errorKey,
						'Leeres Feld'
					);
					return;
				}
				break;
		}
	}

	private function addError(&$errors, $errorKey, $error) {
		$errors[$errorKey] = $error;
	}
	
	private function checkRegex($value, $regex) {
		if (
			\preg_match($regex, $value) == 1
		) {
			return true;
		} else {
			return false;
		}
	}
}