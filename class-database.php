<?php

class Database {
    private $db;
    
    public function __construct($dbname = 'database.sqlite') {
        $this->db = new SQLite3($dbname);
        $this->initialize();
    }
    
    private function initialize() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS books (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            book_name TEXT NOT NULL,
            url TEXT,
            publisher TEXT,
            translator TEXT,
            additional_info TEXT,
            publication_month TEXT,
            age_recommendation TEXT,
            description TEXT,
            season TEXT,
            date_added TEXT DEFAULT CURRENT_TIMESTAMP
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_name TEXT NOT NULL,
            password TEXT NOT  NULL,
            email TEXT NOT NULL
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS sessions (
            sid TEXT PRIMARY KEY,
            user_id INTEGER NOT NULL,
            open_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
        )");
    }
    
    public function insertUser($user_name, $email, $password) {
        $user = $this->getUser($user_name);
        if ($user) {
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (user_name, password, email) VALUES (:user_name, :password, :email)");
        $stmt->bindValue(':user_name', $user_name, SQLITE3_TEXT);
        $stmt->bindValue(':password', $password, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        return $stmt->execute();
    }

    public function getUser($user_name) {
        $query = 'SELECT * FROM users WHERE user_name = :user_name';
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_name', $user_name, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row;
    }

    public function checkPassword($user_name, $password) {
        $user = $this->getUser($user_name);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['password']);
    }

    public function getUsers() {
        $query = "SELECT * FROM users";
        $result = $this->db->query($query);
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            var_dump($row);
        }
        return true;
    }

    public function getSessionUser($sid) {
        $stmt = $this->db->prepare("SELECT * FROM sessions WHERE sid = :sid");
        $stmt->bindValue(':sid', $sid, SQLITE3_TEXT);
        $result = $stmt->execute();
        if (!$result) {
            return false;
        }
        $session = $result->fetchArray(SQLITE3_ASSOC);
        if (isset($session['user_id'])) {
            // Control session length based on $session['open_time'] if necessary.
            return $session['user_id'];
        }
        return false;
    }

    public function setSessionUser($sid, $user_id) {
        $stmt = $this->db->prepare("INSERT INTO sessions (sid, user_id) VALUES (:sid, :user_id)");
        $stmt->bindValue(':sid', $sid, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    public function deleteSession($sid) {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE sid = :sid");
        $stmt->bindValue(':sid', $sid, SQLITE3_TEXT);
        return $stmt->execute();
    }

    public function insertBook($firstName, $lastName, $bookName, $url, $publisher, $translator, $additionalInfo, $publicationMonth, $ageRecommendation, $description, $season, $dateAdded = null) {
        $stmt = $this->db->prepare("INSERT INTO books (first_name, last_name, book_name, url, publisher, translator, additional_info, publication_month, age_recommendation, description, season, date_added) VALUES (:first_name, :last_name, :book_name, :url, :publisher, :translator, :additional_info, :publication_month, :age_recommendation, :description, :season, :date_added)");
        $stmt->bindValue(':first_name', $firstName, SQLITE3_TEXT);
        $stmt->bindValue(':last_name', $lastName, SQLITE3_TEXT);
        $stmt->bindValue(':book_name', $bookName, SQLITE3_TEXT);
        $stmt->bindValue(':url', $url, SQLITE3_TEXT);
        $stmt->bindValue(':publisher', $publisher, SQLITE3_TEXT);
        $stmt->bindValue(':translator', $translator, SQLITE3_TEXT);
        $stmt->bindValue(':additional_info', $additionalInfo, SQLITE3_TEXT);
        $stmt->bindValue(':publication_month', $publicationMonth, SQLITE3_TEXT);
        $stmt->bindValue(':age_recommendation', $ageRecommendation, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':season', $season, SQLITE3_TEXT);
        if (!$dateAdded) {
        	$dateAdded = time();
        }
        $stmt->bindValue(':date_added', $dateAdded, SQLITE3_TEXT);
        return $stmt->execute();
    }

    public function updateBook($id, $firstName, $lastName, $bookName, $url, $publisher, $translator, $additionalInfo, $publicationMonth, $ageRecommendation, $description, $season) {
        $stmt = $this->db->prepare("UPDATE books SET first_name = :first_name, last_name = :last_name, book_name = :book_name, url = :url, publisher = :publisher, translator = :translator, additional_info = :additional_info, publication_month = :publication_month, age_recommendation = :age_recommendation, description = :description, season = :season WHERE id = :id");
        $stmt->bindValue(':first_name', $firstName, SQLITE3_TEXT);
        $stmt->bindValue(':last_name', $lastName, SQLITE3_TEXT);
        $stmt->bindValue(':book_name', $bookName, SQLITE3_TEXT);
        $stmt->bindValue(':url', $url, SQLITE3_TEXT);
        $stmt->bindValue(':publisher', $publisher, SQLITE3_TEXT);
        $stmt->bindValue(':translator', $translator, SQLITE3_TEXT);
        $stmt->bindValue(':additional_info', $additionalInfo, SQLITE3_TEXT);
        $stmt->bindValue(':publication_month', $publicationMonth, SQLITE3_TEXT);
        $stmt->bindValue(':age_recommendation', $ageRecommendation, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':season', $season, SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    public function deleteBook($id) {
    	$query = 'DELETE FROM books WHERE id = :id';
    	$stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
    }
    
    public function getBook($id) {
    	$query = 'SELECT * FROM books WHERE id = :id';
    	$stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row;
    }

    public function getBooks($season = null) {
        $query = "SELECT * FROM books";
        if ($season) {
            $query .= " WHERE season = :season";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':season', $season, SQLITE3_TEXT);
            $result = $stmt->execute();
        } else {
            $query .= " ORDER BY date_added DESC";
            $result = $this->db->query($query);
        }
        $books = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $books[] = $row;
        }
        return $books;
    }

    public function getColumn($column) {
        $legal_columns = array(
            'first_name',
            'last_name',
            'book_name',
            'url',
            'publisher',
            'translator',
            'additional_info',
            'publication_month',
            'age_recommendation',
            'description',
            'season',
            'date_added',
        );
        if ( ! in_array( $column, $legal_columns, true ) ) {
            return array();
        }
        $query = "SELECT DISTINCT($column) FROM books";
        $result = $this->db->query($query);
        $result_array = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $result_array[] = $row[$column];
        }
        sort($result_array);
        return $result_array;
    }

    public function exportToCSV($season) {
        $books = $this->getBooks($season);
        echo "<pre>";
        foreach ($books as $book) {
        	if ($book['url']) {
        		$book['book_name'] = "[{$book['book_name']}]({$book['url']})";
        	}
        	$age_rec = '';
        	if (is_string($book['age_recommendation']) && strlen($book['age_recommendation']) > 0 ) {
	        	$age_rec = ";{$book['age_recommendation']}";
        	}
            $date = $book['date_added'];
            if (is_numeric($date)) {
                $date = date('j.n.Y', $date);
            }
            echo <<<EOCSV
            {$book['first_name']};{$book['last_name']};{$book['book_name']};{$book['publisher']};{$book['translator']}{$age_rec};{$book['additional_info']};{$book['publication_month']};{$date};{$book['description']}

            EOCSV;
        }
        echo "</pre>";
    }

    public function importFromCSV($filename) {
        if (($file = fopen($filename, 'r')) !== false) {
            while (($data = fgetcsv($file, 0, ';', '"', '\\')) !== false) {
                $this->insertBook($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], strtotime($data[11]));
            }
            fclose($file);
        }
    }
}
