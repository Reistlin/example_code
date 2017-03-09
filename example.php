<?

defined('UACCESS') or die('Restricted access');

class Library extends Base_class {

	function authors_add($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $params['author'];
		$name = (string) $params['name'];
		$edition = (int) $params['edition'];

		$fields = [
			'name' => $name,
			'edition' => $edition,
		];

		if ($id) {
			$update = $fields_pr = [];

			foreach ($fields as $key => $value) {
				if (isset($fields[$key])) {
					$update[] = $key . '=?';
					$fields_pr[] = $fields[$key];
				}
			}


			if ($update) {
				$update = implode(',', $update);
				$upd = $db->prepare("UPDATE {$conf['lib_authors']} SET $update WHERE id = $id ");
				$upd->execute($fields_pr);
			}
		} else {
			$add = $db->prepare("INSERT IGNORE INTO {$conf['lib_authors']} (name, edition) VALUES(?,?)");
			$add->execute([$name, $edition]);
		}
		return $this->authors_info($id ? : $db->lastInsertId());
	}

	function authors_info($author) {
		return $this->authors_list(['authors' => $author])[0];
	}

	function authors_list($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$ids = intify_unique($params['authors']);

		$cond = '';

		if ($ids)
			$cond = 'WHERE id IN ( ' . implode(',', $ids) . ' )';

		if ($params['name']) {
			$arr[':name'] = "{$params['name']}%";
			$cond = 'WHERE name LIKE :name';
		}

		$authors = $db->prepare("SELECT id, name, edition FROM {$conf['lib_authors']} $cond ORDER BY id DESC ");
		$authors->execute($arr);
		return $authors->fetchAll(PDO::FETCH_ASSOC);
	}

	function authors_del($author) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $author;

		try {
			$db->exec("DELETE FROM {$conf['lib_authors']} WHERE id = $id ");
		} catch (PDOException $e) {
			return uerror('cant_delete');
		}

		return;
	}

	function publishers_add($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $params['publishers'];
		$name = (string) $params['name'];
		$short_name = (string) $params['short_name'];

		$fields = [
			'name' => $name,
			'short_name' => $short_name,
		];

		if ($id) {
			$update = $fields_pr = [];

			foreach ($fields as $key => $value) {
				if (isset($params[$key])) {
					$update[] = $key . '=?';
					$fields_pr[] = $fields[$key];
				}
			}


			if ($update) {
				$update = implode(',', $update);
				$upd = $db->prepare("UPDATE {$conf['lib_publishers']} SET $update WHERE id = $id ");
				$upd->execute($fields_pr);
			}
		} else {
			$add = $db->prepare("INSERT IGNORE INTO {$conf['lib_publishers']} (name, short_name) VALUES(?, ?)");
			$add->execute([$name, $short_name]);
		}
		return $this->publishers_info($id ? : $db->lastInsertId());
	}

	function publishers_info($publishers) {
		return $this->publishers_list(['publishers' => $publishers])[0];
	}

	function publishers_list($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $params['publishers'];
		$name = (string) $params['name'];
		$cond = '';

		$arr = [];

		if ($id) {
			$arr[':id'] = $id;
			$cond = " WHERE id=:id";
		}

		if ($name) {
			$arr[':name'] = $arr[':name2'] = "{$name}%";
			$cond = 'WHERE name LIKE :name OR short_name LIKE :name2';
		}
		$publishers = $db->prepare("SELECT id, name, short_name FROM {$conf['lib_publishers']} $cond ORDER BY id DESC ");
		$publishers->execute($arr);
		return $publishers->fetchAll(PDO::FETCH_ASSOC);
	}

	function publishers_del($publishers) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $publishers;

		try {
			$db->exec("DELETE FROM {$conf['lib_publishers']} WHERE id = $id ");
		} catch (PDOException $e) {
			return uerror('cant_delete');
		}


		return;
	}

	function sections_add($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $params['section'];
		$name = (string) $params['name'];

		$fields = [
			'name' => $name
		];

		if ($id) {
			$update = $fields_pr = [];

			foreach ($fields as $key => $value) {
				if (isset($fields[$key])) {
					$update[] = $key . '=?';
					$fields_pr[] = $fields[$key];
				}
			}


			if ($update) {
				$update = implode(',', $update);
				$upd = $db->prepare("UPDATE {$conf['lib_sections']} SET $update WHERE id = $id ");
				$upd->execute($fields_pr);
			}
		} else {
			$add = $db->prepare("INSERT IGNORE INTO {$conf['lib_sections']} (name) VALUES(?)");
			$add->execute([$name]);
		}
		return $this->sections_info($id ? : $db->lastInsertId());
	}

	function sections_info($sections) {
		return $this->sections_list(['sections' => $sections])[0];
	}

	function sections_list($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$ids = intify_unique($params['sections']);
		$arr = [];

		if ($ids)
			$cond = 'WHERE id IN ( ' . implode(',', $ids) . ' )';

		if ($params['name']) {
			$arr[':name'] = "{$params['name']}%";
			$cond = 'WHERE name LIKE :name';
		}

		$sections = $db->prepare("SELECT id, name FROM {$conf['lib_sections']} $cond ORDER BY id DESC");
		$sections->execute($arr);
		return $sections->fetchAll(PDO::FETCH_ASSOC);
	}

	function sections_del($section) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $section;


		try {
			$db->exec("DELETE FROM {$conf['lib_sections']} WHERE id = $id ");
		} catch (PDOException $e) {
			return uerror('cant_delete');
		}

		return;
	}

	function collections_add($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $params['collection'];
		$name = (string) $params['name'];
		$type = (int) $params['type'];
		$price = (int) $params['price'];

		$fields = [
			'name' => $name,
			'type' => $type,
			'price' => $price,
		];

		if ($id) {
			$update = [];

			$update = $fields_pr = [];

			foreach ($fields as $key => $value) {
				if (isset($fields[$key])) {
					$update[] = $key . '=?';
					$fields_pr[] = $fields[$key];
				}
			}


			if ($update) {
				$update = implode(',', $update);
				$upd = $db->prepare("UPDATE {$conf['lib_collections']} SET $update WHERE id = $id ");
				$upd->execute($fields_pr);
			}
		} else {
			$add = $db->prepare("INSERT IGNORE INTO {$conf['lib_collections']} (name, type, price) VALUES(?,?,?)");
			$add->execute([$name, $type, $price]);
		}
		return $this->collections_info($id ? : $db->lastInsertId());
	}

	function collections_info($collections) {
		return $this->collections_list(['collections' => $collections])[0];
	}

	function collections_list($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $params['collections'];

		$cond = '';

		if ($id)
			$cond = " WHERE id = $id ";

		return $db->query("SELECT id, name, price, type FROM {$conf['lib_collections']} $cond ORDER BY id DESC ")->fetchAll(PDO::FETCH_ASSOC);
	}

	function collections_del($collection) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $collection;

		try {
			$db->exec("DELETE FROM {$conf['lib_collections']} WHERE id = $id ");
		} catch (PDOException $e) {
			return uerror('cant_delete');
		}

		return;
	}

	function collections_sections_list($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$collection = (int) $params['collection'];
		$section = (int) $params['section'];
		$id = (int) $params['id'];

		if ($collection)
			$cond = " WHERE collection = $collection ";

		if ($section)
			$cond = " WHERE section = $section";

		if ($id)
			$cond = " WHERE id = $id";
		$collections_sections = $db->query("SELECT * FROM {$conf['lib_c_sections']} $cond ORDER BY id DESC ")->fetchAll(PDO::FETCH_ASSOC);
		if($params['pure'])
			return $collections_sections;

		$sections_ids = niceArray($collections_sections, 'section');
		$sections = niceArray($this->sections_list(['sections' => $sections_ids]), ['id' => '*']);
		return [
			'collections_sections' => $collections_sections,
			'sections' => $sections
		];
	}

	function collections_sections_delete($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $params['collections_sections'];

		try {
			$db->exec("DELETE FROM {$conf['lib_c_sections']} WHERE id = $id");
		} catch (PDOException $e) {
			return uerror('cant_delete');
		}
		return;
	}

	function collections_sections_info($id) {
		return $this->collections_sections_list(['id' => $id]);
	}

	function collections_sections_add($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $params['id'];
		$section = (int) $params['section'];
		$collection = (int) $params['collection'];


		$fields = [
		  'section' => $section,
		  'collection' => $collection,
		  ];

		  if($id) {
			  $update = [];

			  $update = $fields_pr = [];

			  foreach ($fields as $key => $value) {
				  if (isset($fields[$key])) {
					  $update[] = $key . '=?';
					  $fields_pr[] = $fields[$key];
				  }
			  }


			  if ($update) {
				  $update = implode(',', $update);
				  $upd = $db->prepare("UPDATE {$conf['lib_c_sections']} SET $update WHERE id = $id ");
				  $upd->execute($fields_pr);
			  }
		  } else {
			$add = $db->prepare("INSERT IGNORE INTO {$conf['lib_c_sections']} (section, collection) VALUES(?,?)");
			$add->execute([$section, $collection]);
		  }
		return $this->collections_sections_info($id ?: $db->lastInsertId());
	}

	function books_list($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$arr[':start'] = (int) $params['start'];
		$collections_sections = (int) $params['collections_sections'];

		if ($params['section'] || $params['collection'])
			$collections_sections = implode(',', niceArray($this->collections_sections_list($params), 'id'));


		if ($collections_sections) {
			$cond[] = "collections_sections  IN($collections_sections)";
		}
		if(isset($params['name'])) {
			$arr[':name'] = "{$params['name']}%";
			$cond[]  = "name LIKE :name";
		}

		if ($params['book']) {
			$ids = implode(',', intify_unique($params['book']));
			$cond[] = "id IN ($ids)";
		}
		if(!$cond)
			return [];

		$cond = implode('AND ', $cond);

		$books = $db->prepare("SELECT * FROM {$conf['lib_books']}  WHERE $cond ORDER BY id DESC LIMIT :start, 30");
		$books->execute($arr);
		$books_ids = niceArray($books, 'id');
		return [
			'book' => $books->fetchAll(PDO::FETCH_ASSOC),
			'authors' => $this->books_authors_list(['books' => $books_ids]),
		];
	}

	function books_info($book_id) {
		$book = $this->books_list(["book" => $book_id]);
		$book['publishers']	= $this->publishers_info($book['book'][0]['publisher']);
		return $book;
	}

	function books_add($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;
		$ftp = $this->ftp();
		$id = (int) $params['book'];
		$authors = (array) $params['json_authors'];
		$publisher = (int) $params['publisher'];
		$publication_type = (int) $params['publication_type'];
		$name = (string) $params['name'];
		$year = (int) $params['year'];
		$description = (string) $params['description'];
		$quantity = (int) $params['quantity'];
		$isbn = (string) $params['isbn'];
		$language = (int) $params['language'];
		$cover = (int) $params['cover'];
		$edition = (int) $params['edition'];
		$format = (string) $params['format'];
		$periodicity = (int) $params['periodicity'];
		$text = (int) $params['text'];
		$target = (int) $params['target'];
		$page = (int) $params['page'];
		$library_address = (int) $params['library_address'];
		$collections_sections = (int) $params['collections_sections'];
		$picture = (string) $params['picture'];
		$publication = (string) $params['publication'];

		$fields = [
			'name' => $name,
			'description' => $description,
			'quantity' => $quantity,
			'publisher' => $publisher,
			'publication_type' => $publication_type,
			'isbn' => $isbn,
			'language' => $language,
			'cover' => $cover,
			'year'	=> $year,
			'edition' => $edition,
			'format' => $format,
			'periodicity' => $periodicity,
			'text' => $text,
			'page' => $page,
			'target' => $target,
			'library_address' => $library_address,
			'collections_sections' => $collections_sections,
		];

		if ($id) {
			$update = $fields_pr = [];

			foreach ($fields as $key => $value) {
				if (isset($fields[$key])) {
					$update[] = $key . '=?';
					$fields_pr[] = $fields[$key];
				}
			}


			if ($update) {
				$update = implode(',', $update);
				$upd = $db->prepare("UPDATE {$conf['lib_books']} SET $update WHERE id = $id ");
				$upd->execute($fields_pr);
			}
		} else {
			$add = $db->prepare("INSERT IGNORE INTO {$conf['lib_books']} (name, year, picture, publication,  description, quantity, page, isbn, publisher, publication_type, language, cover, edition, format, periodicity, text, target, library_address, collections_sections) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
			$add->execute([$name, $year, $picture, $publication, $description, $quantity, $page, $isbn, $publisher, $publication_type, $language, $cover, $edition, $format, $periodicity, $text, $target, $library_address, $collections_sections]);
			$id = $db->lastInsertId();
		}
		$upd = '';
		foreach ($_FILES as $key => $file) {
			$file_info = mb_pathinfo($file['name']);
			if ($key == 'picture' && $file_info['extension'] == 'jpg') {
				if(!$ftp->isDir($dir = 'files/books/'.$id)) $ftp->mkDirRecursive($dir);
				$ftp->put($dir . '/picture.jpg', $file['tmp_name'], FTP_BINARY);
				$upds[] = "picture = '1'";
			} else if($key == 'publication' && $file_info['extension'] == 'pdf') {
				if(!$ftp->isDir($dir = 'files/books/'.$id)) $ftp->mkDirRecursive($dir);
				$ftp->put($dir . '/publication.pdf', $file['tmp_name'], FTP_BINARY);
				$upds[] = "publication = '1'";
			}
		}
		if ($_FILES) {
			$upd = implode(",", $upds);
			$db->exec("UPDATE {$conf['lib_books']} SET $upd WHERE id = $id");
		}

		$this->books_authors_del($id);

		foreach ($authors as $author) {
			$this->books_authors_add(['book' => $id, 'author' => $author['authors']]);
		}

		return $this->books_list(['book' => $id]);
	}

	function books_authors_add($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$book = (int) $params['book'];
		$author = (int) $params['author'];
		$add = $db->prepare("INSERT IGNORE INTO {$conf['lib_b_authors']} (book, author)  VALUES(?,?)");
		$add->execute([$book, $author]);
		return $this->books_authors_info($db->lastInsertId());
	}

	function books_authors_info($id) {
		return $this->books_authors_list(['id' => $id])[0];
	}

	function books_authors_del($book) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$book = (int) $book;
		try {
			$db->exec("DELETE FROM {$conf['lib_b_authors']} WHERE book = $book ");
		} catch (PDOException $e) {
			return uerror('cant_delete');
		}
	}

	function books_authors_list($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$books = intify_unique($params['books']);
		$id = (int) $params['id'];
		$authors = (int) $params['author'];

		if ($books)
			$cond = ' WHERE book  IN ( ' . implode(',', $books) . ' )';

		if ($authors)
			$cond = " WHERE author = $authors ";

		if ($id)
			$cond = " WHERE id = $id";

		$books_authors = $db->query("SELECT * FROM {$conf['lib_b_authors']} $cond ORDER BY id DESC ")->fetchAll(PDO::FETCH_ASSOC);
		$author_ids = niceArray($books_authors, 'author');
		$authors_list = niceArray($this->authors_list(['authors' => $author_ids]), ['id' => '*']);
		return [
			'books_authors' => niceArray($books_authors, ['book' => '**']),
			'authors' => $authors_list
		];
	}

	function books_del($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $params['book'];

		$this->del_file(['book' => $id, 'file' => 'all']);


		try {
			$db->exec("DELETE FROM {$conf['lib_books']} WHERE id = $id ");
		} catch (PDOException $e) {
			return uerror('cant_delete');
		}

		return;
	}

	function del_file($params = []) {
		$ftp = $this->ftp();
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;
		$id = (int) $params['book'];
		$file = (string) $params['file'];
		if (!$id) {
			return;
		}

		if($file == 'all') {
			$ftp->tryDeleteRecursive('files/books/'.$id);
		} else {
			$ftp->tryDeleteRecursive('files/books/'.$id.'/'.$file.'*');
			$db->exec("UPDATE {$conf['lib_books']} SET $file='0' WHERE id = $id");
		}
//		$book = $this->books_list(['book' => $id])[0];
//
//		$update = '';
//
//		if ($file == 'all') {
//		} elseif ($file == 'publication') {
//			$update .= "publication=0";
//		} else {
//			$update .= "picture = 0";
//		}
//
//		$update = "$file = ''";
//
//		foreach ($filename as $key => $address) {
//			array_map(unlink, glob("files/books/$key/{$book['collections_sections']}/$address*"));
//		}
//
//		if ($file != 'all')
//			$db->exec("UPDATE {$conf['lib_books']} SET $update WHERE id = $id");
	}

	function cards_list($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$person = (int) $params['person'];
		$id = (int) $params['id'];

		if($id)
			$id = "WHERE lc.id = $id";

		if ($person)
			$cond = "WHERE lc.person = $person";
		return $db->query("SELECT p.id, p.name FROM {$conf['lib_cards']} lc LEFT JOIN {$conf['persons']} p ON lc.person = p.id $cond GROUP BY lc.person")->fetchAll(PDO::FETCH_ASSOC);
	}

	function card_add($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$person = (int) $params['person'];
		$book_id = (string) $params['book_id'];
		$quantity = (int) $params['quantity'];
		$start_time = (string) $params['start_time'];
		$end_time = (string) $params['end_time'];
		$add = $db->prepare("INSERT IGNORE INTO {$conf['lib_cards']} (person, book_id, quantity, start_time, end_time) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE `quantity` = `quantity`+?, start_time = ?, end_time = ? ");
		$add->execute([$person, $book_id, $quantity, $start_time, $end_time, $quantity, $start_time, $end_time]);

		return $this->card_books(['id' => $db->lastInsertId()]);
	}

	function card_del($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$id = (int) $params['id'];

		try {
			$db->exec("DELETE FROM {$conf['lib_cards']} WHERE id=$id");
		} catch (PDOException $e) {
			return uerror('cant_delete');
		}
	}

	function card_books($params = []) {
		$data = $this->reg;
		$db = $data->get('db');
		$conf = UConfig::$db;

		$person = (int) $params['person'];
		$id = (int) $params['id'];

		if($id)
			$cond = "WHERE id = $id";

		if($person)
			$cond = "WHERE person = $person";

		$card_books = $db->query("SELECT * FROM {$conf['lib_cards']} $cond")->fetchAll(PDO::FETCH_ASSOC);
		$books_ids = niceArray($card_books, 'book_id');

		if(empty($books_ids))
			return [];

		return $this->books_list(['book' => $books_ids]);
	}

}
