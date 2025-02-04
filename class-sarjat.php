<?php

class Sarjat {
	private array $sarjat;

	public function __construct() {
		$db = new Database();
		$books = $db->getBooks();

		$pattern = '/\[(.*?)\]\((.*?)\)/';
		foreach ($books as $book) {
			preg_match_all($pattern, $book['additional_info'], $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$this->sarjat[$match[1]] = "[{$match[1]}]({$match[2]})";
			}
		}
	}

	public function replaceSeries($string) {
		return str_replace(array_keys($this->sarjat), array_values($this->sarjat), $string);
	}
}