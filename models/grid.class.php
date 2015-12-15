<?php
include('./languages/language.php');

class grid
{
	private $gridid = false;
	private $gridtype = 0;
	private $gridletters = '';
	private $gridwordscount = 0;

    private $grid = array();
	private $constraints = false;
    private $all_words = array();
    private $all_words_constraints = array();
    private $points = array();

	private $instruction = '';
	private $constraint = '';
	private $constraint_1 = '';
	private $constraint_2 = '';
	private $word_to_check = '';

	private $countwords_test = 0;
	private $countconstraints_test = 0;
	private $words_test = array();
	private $choose_constraint = '';
	private $choose_number = 0;

	public $userlang = '';

	public function create($gridtype)
	{
		$db = db::getInstance();
		$user = user::getInstance();

		$this->userlang = $user->get_lang();

		// génération de la grille et des mots
		$this->gridtype = $gridtype;
		switch ( $this->gridtype )
		{
			case GRIDTYPE_ALLWORDS:
			case GRIDTYPE_LONGEST:
				$this->constraints = false;
				$this->generate();
			break;
			case GRIDTYPE_CONSTRAINTS:
				$this->constraints = true;
				$this->get_constraints();
			break;
		}

		// stockage dans la bdd
		$this->gridletters = '';
		foreach ( $this->grid as $row )
		{
			$this->gridletters .= implode('', $row);
		}
		$this->gridwordscount = count($this->all_words);

		$sql = 'INSERT INTO grids
					(gridletters, gridwordscount, gridtype, gridconstraint)
					VALUES (' . $db->escape((string) $this->gridletters) . ', '	.				intval($this->gridwordscount) . ', ' .
						intval($this->gridtype) . ', ' .
						intval($this->choose_number) . ')';
		$db->query($sql);
		$this->gridid = $db->next_id();

		// calcul des bonus de chaque mot
		switch ( $this->gridtype )
		{
			case GRIDTYPE_ALLWORDS:
				if ( $this->userlang == 'en' )
				{
					$this->instruction = 'Find as many words';
				}
				if ( $this->userlang == 'it' )
				{
					$this->instruction = 'Trova quante più parole';
				}
				if ( $this->userlang == 'fr' )
				{
					$this->instruction = ' Trouvez autant de mots que possible';
				}
				if ( $this->userlang == 'de' )
				{
					$this->instruction = ' Finden Sie so viele Wörter ';
				}
				if ( $this->userlang == 'es' )
				{
					$this->instruction = ' Encuentre tantas palabras';
				}
				$this->get_bonus_allwords();
			break;
			case GRIDTYPE_LONGEST:
				if ( $this->userlang == 'en' )
				{
					$this->instruction = 'Find the longest words';
				}
				if ( $this->userlang == 'it' )
				{
					$this->instruction = 'Trovare le parole più lunghe';
				}
				if ( $this->userlang == 'fr' )
				{
					$this->instruction = 'Trouvez les mots plus longs';
				}
				if ( $this->userlang == 'de' )
				{
					$this->instruction = 'Finden Sie die langsten Wörter';
				}
				if ( $this->userlang == 'es' )
				{
					$this->instruction = 'Encuentre las palabras mas largas';
				}
				$this->get_bonus_longest();
			break;
			case GRIDTYPE_CONSTRAINTS:
			break;
		}

		// stockage dans la bdd des points de mots
		foreach ( $this->all_words as $word => $points )
		{
			$sql = 'INSERT INTO gridswords
						(gridid, gridword, gridpoints)
						VALUES (' .
							intval($this->gridid) . ', ' .
							$db->escape((string) $word) . ', ' .
							intval($points) . '
						)';
			$db->query($sql);
		}
		return $this->gridid;
	}

	public function read($gridid, $gridlang, $gridtype)
	{
		$db = db::getInstance();
		$user = user::getInstance();

		// récupération du paramétrage du dictionnaire (poids et points par lettres)
		$this->points = array();
        $alphabet = $weights = $points = '';
        include('./resources/' . $user->get_lang() . '.letters.php');
		$this->points = array();
		foreach( $alphabet as $idx => $letter )
		{
			$this->points[$letter] = $points[$idx];
		}

		// lecture des données
		$this->userlang = $user->get_lang();
		$this->gridid = intval($gridid);
		$this->gridtype = intval($gridtype);

		// lire la grille
		$sql = 'SELECT *
					FROM grids
					WHERE gridid = ' . intval($this->gridid);
		$result = $db->query($sql);
		if ( !($row = $result->fetch_assoc()) )
		{
			return false;
		}

		// découper gridletters en grid (tableau(y, x))
		$res = str_split($row['gridletters'], 4);
		$this->grid = array();
		foreach ( $res as $rowdata )
        {
            $this->grid[] = str_split($rowdata);
        }

		// lire les mots
		$this->all_words = array();
		$sql = 'SELECT *
					FROM gridswords
					WHERE gridid = ' . intval($this->gridid);
		$result = $db->query($sql);
		while ( ($row = $result->fetch_assoc()) )
		{
			$this->all_words[ $row['gridword'] ] = intval($row['gridpoints']);
		}

		// calcul des bonus de chaque mot
		switch ( $this->gridtype )
		{
			case GRIDTYPE_ALLWORDS:
				if ( $this->userlang == 'en' )
				{
					$this->instruction = 'Find as many words';
				}
				if ( $this->userlang == 'it' )
				{
					$this->instruction = 'Trova quante più parole';
				}
				if ( $this->userlang == 'fr' )
				{
					$this->instruction = 'Trouvez autant de mots que possible';
				}
				if ( $this->userlang == 'de' )
				{
					$this->instruction = ' Finden Sie so viele Wörter';
				}
				if ( $this->userlang == 'es' )
				{
					$this->instruction = ' Encuentre tantas palabras';
				}
				
			break;
			case GRIDTYPE_LONGEST:
				if ( $this->userlang == 'en' )
				{
					$this->instruction = 'Find the longest words';
				}
				if ( $this->userlang == 'it' )
				{
					$this->instruction = 'Trovare le parole più lunghe';
				}
				if ( $this->userlang == 'fr' )
				{
					$this->instruction = 'Trouvez les mots plus longs';
				}
				if ( $this->userlang == 'de' )
				{
					$this->instruction = 'Finden Sie die langsten Wörter';
				}
				if ( $this->userlang == 'es' )
				{
					$this->instruction = 'Encuentre las palabras mas largas';
				}
				
			break;
			case GRIDTYPE_CONSTRAINTS:
			break;
		}

		// Sélectionner aléatoirement une contrainte parmi 3
		$sql = 'SELECT gridconstraint
						FROM grids
						WHERE gridid = ' . $this->gridid;
		$result = $db->query($sql);
		$row = $result->fetch_assoc();
		$this->choose_number = $row['gridconstraint'];

		if ( $this->choose_number !== 0 )
		{
			$this->choose_constraint = ((string) $this->choose_number);
			if ( $this->userlang == 'en' )
			{
				switch ( $this->choose_constraint )
				{
					case "1":
						$this->instruction = 'Find words ending with ING';
					break;
					case "2":
						$this->instruction = 'Find words ending with ED';
					break;
					case "3":
						$this->instruction = 'Find words ending with S';
					break;
				}
			}
			if ( $this->userlang == 'it' )
			{
				switch ( $this->choose_constraint )
				{
					case "1":
						$this->instruction = 'Trovare le parole femminili';
					break;
					case "2":
						$this->instruction = 'Trovare le parole maschili';
					break;
					case "3":
						$this->instruction = 'Trovare le parole al plurale';
					break;
				}
			}
				if ( $this->userlang == 'fr' )
			{
				switch ( $this->choose_constraint )
				{
					case "1":
						$this->instruction = 'Trouvez des mots qui finissent avec es';
					break;
					case "2":
						$this->instruction = 'Trouvez des mots qui finissent avec ez';
					break;
					case "3":
						$this->instruction = 'Trouvez des mots qui finissent avec is';
					break;
				}
			}
			if ( $this->userlang == 'de' )// if faut préciser les contraintes
			{
				switch ( $this->choose_constraint )
				{
					case "1":
						$this->instruction = '................';
					break;
					case "2":
						$this->instruction = '.....................';
					break;
					case "3":
						$this->instruction = '......................';
					break;
				}
			}
			if ( $this->userlang == 'es' )
			{
				switch ( $this->choose_constraint )
				{
					case "1":
						$this->instruction = 'Encuentre las palabras al femenino';
					break;
					case "2":
						$this->instruction = 'Encuentre las palabras al masculino';
					break;
					case "3":
						$this->instruction = 'Encuentre las palabras al plural';
					break;
				}
			}
		}
		return true;
	}

	public function get()
	{
		$user = user::getInstance();

		$res = new stdClass();
		$res->gridid = $this->gridid;
		$res->grid = $this->grid;
		$res->gridtype = $this->gridtype;
		$res->all_points = array_values($this->all_words);
		$res->all_words = array_keys($this->all_words);
		$res->points = $this->points;
		$res->userlang = $user->get_lang();
		$res->instruction = $this->instruction;
		$res->constraint = $this->constraint;
		$res->word_to_check = $this->word_to_check;
		$res->countconstraints_test = $this->countconstraints_test;
		$res->countwords_test = $this->countwords_test;
		$res->words_test = $this->words_test;
		return $res;
	}

	private function get_points($word)
	{
		if ( empty($word) )
		{
			return 0;
		}
		$letters = str_split(strtoupper($word));
		$count = 0;
		foreach( $letters as $letter )
		{
			$count += $this->points[$letter];
		}
		return $count;
	}

	private function get_bonus_allwords()
	{
		$bonusPoints = array(0, 0, 5, 10, 25, 30, 40, 50, 60);
		foreach ( $this->all_words as $word => $points )
		{
			$word_length = min(8, strlen($word));
			$this->all_words[$word] += $bonusPoints[$word_length];
		}
	}

	private function get_bonus_longest()
	{
		$bonusPoints = array(0, 0, 1, 2, 8, 50, 90, 120, 150);
		foreach ( $this->all_words as $word => $points )
		{
			$word_length = min(8, strlen($word));
			$this->all_words[$word] += $bonusPoints[$word_length];
		}
	}

	private function get_constraints()
	{
		$user = user::getInstance();
		$this->userlang = $user->get_lang();

		$bonusPoints = 10;

		$this->choose_number = rand(1,3);
		// Sélectionner aléatoirement une contrainte parmi 3
		$this->choose_constraint = ((string) $this->choose_number);

		if ( $this->userlang == 'en' )
		{
			switch ( $this->choose_constraint )
			{
				case "1":
					$this->instruction = 'Find words ending with ING';
					$this->constraint = 'ING';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( substr($this->word_to_check, -3) == 'ING' )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				case "2":
					$this->instruction = 'Find words ending with ED';
					$this->constraint = 'EDD';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( substr($word, -2) == 'ED' )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				case "3":
					$this->instruction = 'Find words ending with S';
					$this->constraint = 'SSS';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( substr($this->word_to_check, -1) == 'S' )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				default:
					trigger_error('Game not found!', E_USER_ERROR);
				break;
			}
		}
		if ( $this->userlang == 'it' )
		{
			switch ( $this->choose_constraint )
			{
				case "1":
					$this->instruction = 'Trovare le parole femminili';
					$this->constraint = 'AE';
					//$this->constraint_2 = 'E';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( (substr($word, -1) == 'A') || (substr($word, -1) == 'E') )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				case "2":
					$this->instruction = 'Trovare le parole maschili';
					$this->constraint = 'OI';
					//$this->constraint_2 = 'I';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( (substr($word, -1) == 'O') || (substr($word, -1) == 'I') )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				case "3":
					$this->instruction = 'Trovare le parole al plurale';
					$this->constraint = 'IE';
					//$this->constraint_2 = 'E';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( (substr($word, -1) == 'I') || (substr($word, -1) == 'E') )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				default:
					trigger_error('Game not found!', E_USER_ERROR);
				break;
			}
		}// ce block de " if "faut adapter à la langue française
		if ( $this->userlang == 'fr' )
		{
			switch ( $this->choose_constraint )
			{
				case "1":
					$this->instruction = 'Les mots qui finissent avec es';
					$this->constraint = 'ES';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( substr($this->word_to_check, -2) == 'ES' )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				case "2":
					$this->instruction = 'Les mots qui finissent avec ez';
					$this->constraint = 'EZ';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( substr($word, -2) == 'EZ' )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				case "3":
					$this->instruction = 'Les mots qui finissent avec is';
					$this->constraint = 'IS';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( substr($this->word_to_check, -2) == 'IS' )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				default:
					trigger_error('Game not found!', E_USER_ERROR);
				break;
			}
		}
		if ( $this->userlang == 'es' )
		{
			switch ( $this->choose_constraint )
			{
				case "1":
					$this->instruction ='Encuentre las palabras al femenino';
					$this->constraint = 'AS';
					//$this->constraint_2 = 'E';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( (substr($word, -1) == 'A') || (substr($word, -2) == 'AS') )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				case "2":
					$this->instruction = 'Encuentre las palabras al masculino';
					$this->constraint = 'OS';
					//$this->constraint_2 = 'I';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( (substr($word, -1) == 'O') || (substr($word, -2) == 'OS') )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				case "3":
					$this->instruction = 'Encuentre las palabras al plural';
					$this->constraint = 'S';
					//$this->constraint_2 = 'E';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( (substr($word, -2) == 'OS') || (substr($word, -2) == 'AS') )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				default:
					trigger_error('Game not found!', E_USER_ERROR);
				break;
			}
		}
		// il faut préciser des contraintes
		if ( $this->userlang == 'de' )
		{
			switch ( $this->choose_constraint )
			{
				case "1":
					$this->instruction ='...............';
					$this->constraint = '';
					//$this->constraint_2 = 'E';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( (substr($word, -1) == 'A') || (substr($word, -2) == 'AS') )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				case "2":
					$this->instruction = '............';
					$this->constraint = 'OS';
					//$this->constraint_2 = 'I';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( (substr($word, -1) == 'O') || (substr($word, -2) == 'OS') )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				case "3":
					$this->instruction = '.........................';
					$this->constraint = 'S';
					//$this->constraint_2 = 'E';
					$this->generate_constraints();
					foreach ( $this->all_words as $word => $points )
					{
						$this->word_to_check = $word;
						if ( (substr($word, -2) == 'OS') || (substr($word, -2) == 'AS') )
						{
							$this->all_words[$word] *= $bonusPoints;
						}
					}
				break;
				default:
					trigger_error('Game not found!', E_USER_ERROR);
				break;
			}
		}
	}

	// génération du jeu
    public function generate()
    {
		for ( $i = 0; $i < 1; $i++ )
		{
			$this->grid = array();
			$this->all_words = array();

			$best_grid = array();
			$best_count = 0;

			$count = 0;
			while ( $best_count < 50 )
			{
				$count++;

				// Tirage aléatoire des lettres, grid est un tableau de colonnes par lignes
				$this->grid = $this->create_grid();

				// Détection de l'ensemble des mots
				$this->search_words();

				$countwords = count($this->all_words);
				if ( $countwords >= $best_count )
				{
					$best_grid = $this->grid;
					$best_count = $countwords;
				}
			}
		}
    }

	// génération du jeu
    public function generate_constraints()
    {
		for ( $i = 0; $i < 1; $i++ )
		{
			$this->grid = array();
			$this->all_words = array();

			$best_grid = array();
			$best_count = 0;
			$best_constraints = 0;

			$count = 0;
			$countwords = 0;
			$countconstraints = 0;

			// trouver plus de 20 mots respectant la contrainte donnée
			while ( ($best_count < 100) || ($best_constraints < 10) )
			{
				$count++;
				$this->words_test = array();
				$countwords = 0;
				$countconstraints = 0;

				// Tirage aléatoire des lettres, grid est un tableau de colonnes par lignes
				$this->grid = $this->create_grid();

				// Détection de l'ensemble des mots
				$this->search_words();

				$countwords = count($this->all_words);
				if ( $countwords >= $best_count )
				{
					$best_grid = $this->grid;
					$best_count = $countwords;
					$this->countwords_test = $countwords;
				}

				// stockage dans la bdd des points de mots en fonction des contraintes
				foreach ( $this->all_words as $word => $points )
				{
					if ( $this->userlang == 'en' )
					{
						switch ( $this->choose_constraint )
						{
							case "1":
								$get_constraint = substr($word, -3);
								$constraint = 'ING';
							break;
							case "2":
								$get_constraint = substr($word, -2);
								$constraint = 'ED';
							break;
							case "3":
								$get_constraint = substr($word, -1);
								$constraint = 'S';
							break;
							default:
								trigger_error('Game not found!', E_USER_ERROR);
							break;
						}
						if ( $get_constraint == $constraint )
						{
							$countconstraints++;
							array_push($this->words_test, $word);
						}
					}
					if ( $this->userlang == 'it' )
					{
						switch ( $this->choose_constraint )
						{
							case "1":
								$get_constraint = substr($word, -1);
								$constraint_1 = 'A';
								$constraint_2 = 'E';
							break;
							case "2":
								$get_constraint = substr($word, -1);
								$constraint_1 = 'O';
								$constraint_2 = 'I';
							break;
							case "3":
								$get_constraint = substr($word, -1);
								$constraint_1 = 'E';
								$constraint_2 = 'I';
							break;
							default:
								trigger_error('Game not found!', E_USER_ERROR);
							break;
						}
						if ( $get_constraint == $constraint_1 || $get_constraint == $constraint_2 )
						{
							$countconstraints++;
							array_push($this->words_test, $word);
						}
					}// ce block de "if" faut modifier pour le français
					if ( $this->userlang == 'fr' )
					{
						switch ( $this->choose_constraint )
						{
							case "1":
								$get_constraint = substr($word, -2);
								$constraint = 'ES';
							break;
							case "2":
								$get_constraint = substr($word, -2);
								$constraint = 'EZ';
							break;
							case "3":
								$get_constraint = substr($word, -2);
								$constraint = 'IS';
							break;
							default:
								trigger_error('Game not found!', E_USER_ERROR);
							break;
						}
						if ( $get_constraint == $constraint )
						{
							$countconstraints++;
							array_push($this->words_test, $word);
						}
					}
						if ( $this->userlang == 'es' )
					{
						switch ( $this->choose_constraint )
						{
							case "1":
								$get_constraint = substr($word, -2);
								$constraint_1 = 'A';
								$constraint_2 = 'AS';
							break;
							case "2":
								$get_constraint = substr($word, -2);
								$constraint_1 = 'O';
								$constraint_2 = 'OS';
							break;
							case "3":
								$get_constraint = substr($word, -2);
								$constraint_1 = 'AS';
								$constraint_2 = 'OS';
							break;
							default:
								trigger_error('Game not found!', E_USER_ERROR);
							break;
						}
						if ( $get_constraint == $constraint_1 || $get_constraint == $constraint_2 || substr($get_constraint,-1)==$constraint_1)
						{
							$countconstraints++;
							array_push($this->words_test, $word);
						}
					}
					//IL FAUT PRECISER LES CONTRAINTES POUR ALLEMAND
						if ( $this->userlang == 'de' )
					{
						switch ( $this->choose_constraint )
						{
							case "1":
								$get_constraint = substr($word, -1);
								$constraint_1 = 'A';
								$constraint_2 = 'E';
							break;
							case "2":
								$get_constraint = substr($word, -1);
								$constraint_1 = 'O';
								$constraint_2 = 'I';
							break;
							case "3":
								$get_constraint = substr($word, -1);
								$constraint_1 = 'E';
								$constraint_2 = 'I';
							break;
							default:
								trigger_error('Game not found!', E_USER_ERROR);
							break;
						}
						if ( $get_constraint == $constraint_1 || $get_constraint == $constraint_2 )
						{
							$countconstraints++;
							array_push($this->words_test, $word);
						}
					}
				}
				if ( $countconstraints >= $best_constraints )
				{
					$best_grid = $this->grid;
					$best_constraints = $countconstraints;
					$this->countconstraints_test = $countconstraints;
				}
			}
		}
    }

    public function update()
    {

    }

	// tirage aléatoire des lettres
    private function create_grid()
    {
		$user = user::getInstance();

		// récupération du paramétrage du dictionnaire (poids et points par lettres)
        $alphabet = $weights = $points = '';
        include('./resources/' . $user->get_lang() . '.letters.php');
		$this->points = array();
		foreach( $alphabet as $idx => $letter )
		{
			$this->points[$letter] = $points[$idx];
		}

		// application du poids à chaque lettres en la multipliant par le poids
        $pondareted = '';
		foreach ( $alphabet as $idx => $letter )
        {
            $weight = $weights[$idx];
            $pondareted .= str_repeat($letter, $weight);
        }

		// Mélange aléatoire de la chaîne pondérée
		$wpondareted = str_shuffle(str_shuffle($pondareted));
		if ( $this->constraints )
		{
			// retenir 16 caractères
			if ( $this->userlang == 'en' )
			{
				$wpondareted = substr($wpondareted, 0, 13);
			}
			if ( $this->userlang == 'it' )
			{
				$wpondareted = substr($wpondareted, 0, 14);
			}
			if ( $this->userlang == 'fr' )
			{
				$wpondareted = substr($wpondareted, 0, 14);
			}
			//IF FAUT CALCULER POUR L'ALLEMAND
			if ( $this->userlang == 'de' )
			{
				$wpondareted = substr($wpondareted, 0, 14);
			}
			if ( $this->userlang == 'es' )
			{
				$wpondareted = substr($wpondareted, 0, 14);
			}
			$wpondareted .= $this->constraint;
			$wpondareted = str_shuffle($wpondareted);
		}
		if ( !$this->constraints )
		{
			// retenir 16 caractères
			$wpondareted = substr($wpondareted, 0, 16);
			$wpondareted = str_shuffle($wpondareted);
		}

		// découpe les 16 caractères en tableau de 4 caractères par ligne
		$res = str_split($wpondareted, 4);

		// fait un tableau de colonnes par lignes (y, x)
		$final = array();
		foreach ( $res as $row )
        {
            $final[] = str_split($row);
        }
        return $final;
    }

    private function search_words()
    {
        // Mise à disposition de la fonction de la base de données
		$db = db::getInstance();

		$user = user::getInstance();

		// tableau des mots potentiels
		$words = array();

		// on l'alimente par récursivité avec la méthode next_letter() en partant de chaque lettre
		for ( $y = 0; $y < 4; $y++ )
        {
            for ( $x = 0; $x < 4; $x++ )
            {
                // on travaille sur une copie de la grille car celle-ci est détruite au fur et à mesure
				$wgrid = $this->grid;

				// ajout au tableau des mots potentiels de ceux qui commencent par la lettre en x, y
                $words = array_merge($words, $this->next_letter('', $wgrid, $x, $y));
            }
        }

        // words contient tous les débuts de mots ayant été trouvé dans le dictionnaire
        // il faut vérifier si chaque word existe réellement dans le dictionnaire
        $this->all_words = array();
        if ( $words )
        {
            $sql = 'SELECT DISTINCT UPPER(form) AS up_form
                        FROM dico_' . $user->get_lang() . '
                        WHERE form IN(' . implode(', ', array_map(array($db, 'escape'), $words)) . ')';
            $result = $db->query($sql);
            while ( ($row = $result->fetch_assoc()) )
            {
				// changer les lettres accentués en lettre sans accent
				
				$word = $this->cleartext($row['up_form']);
					

				// ne conserver que les mots purement alphabétique (mysql retourne les mots avec accents, espace, etc.)
				if ( preg_match('#^[A-Z]+$#', $word) )
				{
	                $this->all_words[$word] = $this->get_points($word);
				}
            }
            $result->free();
        }
    }

	private function cleartext($s)
	{
		setlocale(LC_ALL, 'en_US.UTF8');
		$r = '';
		$s1 = @iconv('UTF-8', 'ASCII//TRANSLIT', $s);
		$j = 0;
		$s1len = strlen($s1);
		for ( $i = 0; $i < $s1len; $i++ )
		{
			$ch1 = $s1[$i];
			$ch2 = @mb_substr($s, $j++, 1, 'UTF-8');
			if ( strstr('`^~\'"', $ch1) !== false )
			{
				if ( $ch1 != $ch2)
				{
					--$j;
					continue;
				}
			}
			$r .= $ch1 == '?' ? $ch2 : $ch1;
		}
		return $r;
	}

    private function next_letter($word, $wgrid, $x, $y)
    {
        $db = db::getInstance();
		$user = user::getInstance();

		// ajouter la lettre en x, y au mot courant
        $word .= $wgrid[$y][$x];

		// la détruire dans la grille
        $wgrid[$y][$x] = '_';

		// vérifier en bdd s'il existe des mots qui commencent par $word à partir de 2 lettres
		if ( strlen($word) > 1 )
		{
			$exists = false;
			$sql = 'SELECT *
						FROM dico_' . $user->get_lang() . '
						WHERE form LIKE \'' . $word . '%\'
						LIMIT 1';
			$result = $db->query($sql);
			$exists = ($row = $result->fetch_assoc()) ? true : false;
			$result->free();

			// si pas de mot dans le dico commençant par le mot en cours, ne pas retourner le mot et arrêter la recherche
			if ( !$exists )
			{
				return array();
			}
		}

        // stocker le début de mot dès qu'il atteint 2 lettres
        $words = strlen($word) > 1 ? array($word) : array();

        //
		// On calcule la position de la lettre suivante (autour de la lettre en cours)
		//

		// ligne du haut
		$yy = $y - 1;
        if ( $yy >= 0 )
        {
            $xx = $x - 1;
            // on vérifie que la lettre n'a pas été utilisée (donc détruite)
			if ( ($xx >= 0) && ($wgrid[$yy][$xx] != '_') )
            {
                // appel en récursif pour ajout d'une nouvelle lettre au mot
				$words = array_merge($words, $this->next_letter($word, $wgrid, $xx, $yy));
            }

            $xx = $x;
            if ( $wgrid[$yy][$xx] != '_' )
            {
                $words = array_merge($words, $this->next_letter($word, $wgrid, $xx, $yy));
            }

            $xx = $x + 1;
            if ( ($xx < 4) && ($wgrid[$yy][$xx] != '_') )
            {
                $words = array_merge($words, $this->next_letter($word, $wgrid, $xx, $yy));
            }
        }

        // ligne du milieu
        $yy = $y;
        $xx = $x - 1;
        if ( ($xx >= 0) && ($wgrid[$yy][$xx] != '_') )
        {
            $words = array_merge($words, $this->next_letter($word, $wgrid, $xx, $yy));
        }
        $xx = $x + 1;
        if ( ($xx < 4) && ($wgrid[$yy][$xx] != '_') )
        {
            $words = array_merge($words, $this->next_letter($word, $wgrid, $xx, $yy));
        }

        // ligne du bas
		$yy = $y + 1;
        if ( $yy < 4 )
        {
            $xx = $x - 1;
            if ( ($xx >= 0) && ($wgrid[$yy][$xx] != '_') )
            {
                $words = array_merge($words, $this->next_letter($word, $wgrid, $xx, $yy));
            }

            $xx = $x;
            if ( $wgrid[$yy][$xx] != '_' )
            {
                $words = array_merge($words, $this->next_letter($word, $wgrid, $xx, $yy));
            }

            $xx = $x + 1;
            if ( ($xx < 4) && ($wgrid[$yy][$xx] != '_') )
            {
                $words = array_merge($words, $this->next_letter($word, $wgrid, $xx, $yy));
            }
        }

        // renvoyer les débuts de mot trouvés
        return $words;
    }

}

?>