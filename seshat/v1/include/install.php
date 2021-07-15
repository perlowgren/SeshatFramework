<?php

namespace Seshat;

use Seshat\SetSession;

function install_db(&$db) {
	$tm = time();

	$table = <<<'SQL'
CREATE TABLE session (
  sid STRING PRIMARY KEY NOT NULL,
  data STRING,
  created INTEGER,
  changed INTEGER
)
SQL;
	$db->exec($table);

	$table = <<<'SQL'
CREATE TABLE user (
  uid INTEGER PRIMARY KEY NOT NULL,
  user STRING,
  pass STRING,
  email STRING,
  lang STRING,
  data STRING,
  auth INTEGER,
  created INTEGER,
  changed INTEGER
)
SQL;
	$db->exec($table);

	$db->exec('CREATE INDEX user_user ON user (user)');
	$db->exec('CREATE INDEX user_email ON user (email)');

	$users = _('install-users');
	if(is_array($users))
		foreach($users as $user=>$data) {
			$pass = md5($data['pass']);
			$email = strtolower($data['email']);
			$lang = $data['lang'];
			$auth = $data['auth'];
			$db->exec('INSERT INTO user (uid,user,pass,email,lang,data,auth,created,changed) VALUES (NULL,?,?,?,?,?,?,?,?)',
							array($user,$pass,$email,$lang,'',$auth,$tm,$tm));
		}

	$table = <<<'SQL'
CREATE TABLE news (
  nid INTEGER PRIMARY KEY NOT NULL,
  uid INTEGER,
  lang STRING,
  subject STRING,
  html STRING,
  text STRING,
  created INTEGER,
  changed INTEGER
)
SQL;
	$db->exec($table);

	$db->exec('CREATE INDEX news_lang ON news (lang)');
	$db->exec('CREATE INDEX news_created ON news (created)');

	$table = <<<'SQL'
CREATE TABLE wiki (
  pid INTEGER PRIMARY KEY NOT NULL,
  rid INTEGER,
  uid INTEGER,
  name STRING,
  prefix STRING,
  page STRING,
  lang STRING,
  html STRING,
  links STRING,
  read INTEGER,
  write INTEGER,
  version INTEGER,
  created INTEGER,
  changed INTEGER
)
SQL;
	$db->exec($table);

	$db->exec('CREATE INDEX wiki_name ON wiki (name)');
	$db->exec('CREATE INDEX wiki_prefix ON wiki (prefix)');
	$db->exec('CREATE INDEX wiki_page ON wiki (page)');
	$db->exec('CREATE INDEX wiki_lang ON wiki (lang)');

	$table = <<<'SQL'
CREATE TABLE wiki_history (
  rid INTEGER PRIMARY KEY NOT NULL,
  pid INTEGER,
  uid INTEGER,
  name STRING,
  text STRING,
  version INTEGER,
  created INTEGER,
  time INTEGER
)
SQL;
	$db->exec($table);
}

