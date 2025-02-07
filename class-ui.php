<?php

class UI {
	private $status;
	private $book_values;
	private $db;
	private $book_action;
	private $mode;
	private $months;

	const UPDATE = 'update';

	public function __construct($db) {
        $this->db = $db;
        $this->book_action = "Tallenna kirja";
		$this->book_values = array(
			'first_name' => '',
			'last_name' => '',
			'book_name' => '',
			'url' => '',
			'publisher' => '',
			'translator' => '',
			'additional_info' => '',
			'publication_month' => '',
			'age_recommendation' => '',
			'description' => '',
			'season' => '',
		);
		$this->mode = 'add';

		$this->months = array(
			'tammikuu',
			'helmikuu',
			'maaliskuu',
			'huhtikuu',
			'toukokuu',
			'kesäkuu',
			'heinäkuu',
			'elokuu',
			'syyskuu',
			'lokakuu',
			'marraskuu',
			'joulukuu',
		);
    }

	public function setStatus($status, $message) {
		$this->status[$status] = $message;
	}
	
	public function setBookAction($book_action) {
		if ($book_action === self::UPDATE) {
			$this->book_action = 'Päivitä kirja';
			$this->mode = self::UPDATE;
		}
	}

	public function setValues($book_values) {
		$this->book_values = $book_values;
	}

	public function setBookValue($key, $value) {
		$this->book_values[$key] = $value;
	}

	public function showHeader() {
		?>
<!DOCTYPE html>
<html>
<head>
    <title>Kirjavinkkien uutuuskirjalista</title>
    <style>
    	table {
		    max-width: 100%;
		    border-collapse: collapse;
		    font-family: Helvetica Neue, Arial, sans-serif;
		    font-size: 16px;
		    margin-top: 20px;
		}

		th, td {
		    padding: 12px;
		    border: 1px solid #ddd;
		    text-align: left;
		}

		th {
		    background-color: #f4f4f4;
		    font-weight: bold;
		}

		tr:nth-child(even) {
		    background-color: #f9f9f9;
		}

		tr:hover {
		    background-color: #f1f1f1;
		}

		td a {
		    color: #007bff;
		    text-decoration: none;
		}

		td a:hover {
		    text-decoration: underline;
		}

		form, div.card {
		    max-width: 500px;
		    margin: 20px auto;
		    padding: 20px;
		    background: #f9f9f9;
		    border-radius: 8px;
		    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		    font-family: Arial, sans-serif;
		}

		label {
		    display: block;
		    font-weight: bold;
		    margin-bottom: 5px;
		    color: #333;
		}

		input, button, textarea, select {
		    width: 100%;
		    padding: 10px;
		    margin-bottom: 15px;
		    border: 1px solid #ddd;
		    border-radius: 5px;
		    font-size: 16px;
		    box-sizing: border-box;
		    font-family: Helvetica Neue, Arial, sans-serif;
		}

		input:focus {
		    border-color: #007bff;
		    outline: none;
		}

		button {
		    background: #007bff;
		    color: white;
		    font-weight: bold;
		    border: none;
		    cursor: pointer;
		    transition: background 0.3s ease;
		}

		button:hover {
		    background: #0056b3;
		}

		svg {
			width: 32px;
			height: 32px;
		}

		.container {
			display: grid;
			grid-template-columns: 1fr 1fr;
			grid-gap: 10px;
		}

		.warning, .success, .confirm {
		    padding: 15px;
		    border-radius: 5px;
		    font-size: 16px;
		    margin: 20px 0;
		    font-family: Helvetica Neue, Arial, sans-serif;
		}

		.success {
		    background-color: #d9ffb3;
		    color: #00802b;
		    border-left: 5px solid #40ff00;
		}

		.warning, .confirm {
		    background-color: #fff3cd;
		    color: #856404;
		    border-left: 5px solid #ffcc00;
		}

		.confirm a {
			color: #00802b;
		}

		.warning strong, .success strong {
		    font-weight: bold;
		}
	</style>
</head>
<body>
		<?php
	}

	public function displayStatus() {
		if ( isset($this->status['warning']) ) :
			?>
		<div class="warning"><strong>Varoitus:</strong> <?php echo $this->status['warning']; ?></div>
			<?php
		endif;
		if ( isset($this->status['success']) ) :
			?>
		<div class="success"><strong>Onnistui:</strong> <?php echo $this->status['success']; ?></div>
			<?php
		endif;
		if ( isset($this->status['confirm']) ) :
			?>
		<div class="confirm"><strong><?php echo $this->status['confirm']; ?></strong>
			<a href="?confirm=<?php echo $this->status['action']; ?>&id=<?php echo $this->status['id']; ?>&nonce=<?php echo time(); ?>">Kyllä!</a>
		</div>
			<?php
		endif;
	}

	public function render() {
		$svg = new SVG();

		$books = $this->db->getBooks();
		$publishers = $this->db->getColumn('publisher');
		$seasons = $this->db->getColumn('season');
		$translators = $this->db->getColumn('translator');

		$this->displayStatus();
		?>
	<div class="container">
		<div class="block">
		    <form method="post">
		        <label>Etunimi:</label>
		        <input type="text" name="first_name" autofocus data-1p-ignore required value="<?php echo $this->book_values['first_name']?>">
		        <label>Sukunimi:</label>
		        <input type="text" name="last_name" data-1p-ignore required value="<?php echo $this->book_values['last_name']?>">
		        <label>Kirjan nimi:</label>
		        <input type="text" name="book_name" required value="<?php echo $this->book_values['book_name']?>">
		        <label>URL:</label>
		        <input type="text" name="url" value="<?php echo $this->book_values['url']?>">
		        <label>Julkaisija: <span id="uusi_julkaisija" onclick="replaceSelectWithInput('publisher')">(lisää uusi)</span></label>
		        <select type="text" name="publisher" id="publisher">
		        	<?php
		        	foreach ($publishers as $publisher) {
		        		$selected = $this->book_values['publisher'] === $publisher ? 'selected="selected"' : '';
		        		echo "<option $selected>$publisher</option>";
		        	}
		        	?>
		        </select>
		        <label>Kääntäjä:</label>
		        <input type="text" name="translator" list="translators" value="<?php echo $this->book_values['translator']?>">
		        <label>Lisätiedot:</label>
		        <input type="text" name="additional_info" value="<?php echo $this->book_values['additional_info']?>">
		        <div class="container">
			        <div>
			       	 	<label>Kuukausi:</label>
			        	<select type="text" name="publication_month">
			        		<?php foreach ( $this->months as $month ) {
			        			$selected = monthSelected($this->book_values, $month);
			        			echo "<option $selected>$month</option>";
			        		} ?>
				        </select>
				    </div>
				    <div>
			        	<label>Ikäsuositus:</label>
			        	<input type="text" name="age_recommendation"  value="<?php echo $this->book_values['age_recommendation']?>">
			        </div>
			    </div>
		        <label>Kuvaus:</label>
		        <textarea type="text" name="description" cols="80" rows="3"><?php echo $this->book_values['description']?></textarea>
		        <label>Kausi:</label>
		        <input type="text" name="season" list="seasons" required value="<?php echo $this->book_values['season']?>">
		        <button type="submit"><?php echo $this->book_action; ?></button>
		        <?php if ($this->mode === self::UPDATE) : ?>
		        	<input type="hidden" name="id" value="<?php echo $this->book_values['id'] ?>" />
		        	<input type="hidden" name="update_book" value="<?php echo time(); ?>" />
		        	<a href="/">Peru muokkaus</a>
		        <?php endif; ?>
		    </form>
		</div>
		<div class="block">
			<div class="card">
				<label>YKL:</label>
				<input type="text" name="ykl" id="ykl">
				<div id="autocompleteResult"></div>
			</div>
			<form method="post" enctype="multipart/form-data">
		        <label>Tuo CSV:</label>
		        <input type="file" name="csv_file" required>
		        <button type="submit">Tuo CSV</button>
		    </form>
		    <form method="get">
		        <label>Vie kausi:</label>
		        <select name="season">
		        	<?php
		        	foreach ($seasons as $season) {
		        		echo "<option>$season</option>";
		        	}
		        	?>
		        </select>
		        <button type="submit" name="export" value="true">Vie CSV:ksi</button>
		    </form>
		    <form method="post">
		    	<label>Käyttäjätunnus:</label>
		        <input type="text" name="username" autofocus data-1p-ignore required>
		        <label>Email:</label>
		        <input type="text" name="email" data-1p-ignore required>
		        <input type="hidden" name="action" value="create_user">
				<button type="submit">Luo käyttäjä</button>
			</form>
			<form method="post">
		    	<input type="hidden" name="action" value="logout">
				<button type="submit">Kirjaudu ulos</button>
			</form>
		</div>
	</div>
    <h2>Kirjat</h2>
    <table>
    	<thead>
    		<tr>
    			<th>Muoks</th>
    			<th>Tekijä</th>
    			<th>Kirja</th>
    			<th>Julkaisija</th>
    			<th>Suomentaja</th>
    			<th>Luokitus</th>
    			<th>Ikä</th>
    			<th>Kuvaus</th>
    		</tr>
    	</thead>
        <?php foreach ($books as $book): ?>
        	<?php if ($book['url']) {
        		$book['book_name'] = "<a href='{$book['url']}'>{$book['book_name']}</a>";
        	}
        	?>
            <tr><?php
            $time = time();
            $edit = "<a href='?edit={$book['id']}'>{$svg->getEditIcon()}</a>";
            $trash = "<a href='?delete={$book['id']}&nonce={$time}'>{$svg->getTrashIcon()}</a>";
            
            echo <<<EOH
            	<td>$edit $trash</td>
            	<td>{$book['first_name']} {$book['last_name']}</td>
            	<td>{$book['book_name']}</td>
            	<td>{$book['publisher']}</td>
            	<td>{$book['translator']}</td>
            	<td>{$book['additional_info']}</td>
            	<td>{$book['age_recommendation']}</td>
            	<td>{$book['description']}</td>
            </tr>
            EOH;
            ?>
        <?php endforeach; ?>
    </table>

    <datalist id="translators">
	<?php 
		foreach ( $translators as $translator ) {
			echo "<option value='$translator' />";
		}
	?>
	</datalist>

    <datalist id="seasons">
	<?php 
		foreach ( $seasons as $season ) {
			echo "<option value='$season' />";
		}
	?>
	</datalist>

<script>
	<?php 
	$ykl = new YKL();
	echo $ykl->getYklJSArray();
	?>

	document.getElementById("ykl").addEventListener("input", function() {
        const input = this.value.toLowerCase();
        const resultDiv = document.getElementById("autocompleteResult");

        if (yklData[input]) {
            resultDiv.textContent = yklData[input]; // Show matching value
        } else {
            resultDiv.textContent = ""; // Clear result if no match
        }
    });

	function replaceSelectWithInput(selectId) {
    	let selectElement = document.getElementById(selectId);
    	if (!selectElement) return;

    	let inputElement = document.createElement("input");
    	inputElement.type = "text";
    	inputElement.name = selectElement.name;
    	inputElement.value = selectElement.value;
    	inputElement.id = selectElement.id; // Maintain the same ID if needed
    	inputElement.className = selectElement.className; // Keep styling consistent

    	selectElement.parentNode.replaceChild(inputElement, selectElement);
	}

</script>

</script>
</body>
</html>

		<?php
	}
}