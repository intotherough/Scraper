<?php
class ScraperModel
{
	private $_tableName;
	private $_primaryKey;
	private $_affectedRows;

	public function __construct($tableName, $primaryKey = 'id')
	{
		$this->_tableName = $tableName;
		$this->_primaryKey = $primaryKey;
	}

	private function getTableName()
	{
		return $this->_tableName;
	}

	private function getPrimaryKey()
	{
		return $this->_primaryKey;
	}
	/**
	 * Save the data we have scraped from an associative array
	 * Column names are derived from the "header" portion, rows themselves from the "content" portion
	 * @param array $data
	 * @return Ambigous <number, boolean>
	 */
	public function saveData($data)
	{
		$header = $data['header'];
		$rows = $data['content'];

		$dbh = $this->getConnection();
		$total = 0;

		$query = "INSERT INTO {$this->getTableName()} SET ";
		foreach ($header as $colName) {
			$query .= "$colName = :$colName,";
		}
		$stmt = $dbh->prepare(substr($query, 0, -1));

		foreach ($rows as $row) {
			foreach ($row as $index => $val) {
				$stmt->bindValue(":{$header[$index]}", $val);
			}

			try {
				$total += $stmt->execute();
			} catch (PDOException $e) {
				print "Error in exec: " . $e->getMessage() . "<br/>";
			}
		}

		return $total;
	}
	/**
	 * Perform the operations necessary to get the environment ready for a new scrape
	 * Enter description here ...
	 */
	public function emptyForInsert()
	{
		$dbh = $this->getConnection();

		return $dbh->query("TRUNCATE TABLE {$this->getTableName()}");
	}

	//REFACTOR
	//FIXME: creates new on every call - member var
	private function getConnection()
	{
		try {
			$dbh = new PDO('mysql:host=localhost;dbname=heroes_of_coal', 'root', 'ssJ623FP');
		} catch (PDOException $e) {
			print "Error: " . $e->getMessage() . "<br/>";
			return false;
		}
		//allow pdo to throw errors for bad queries, which we'll catch later
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $dbh;
	}
}