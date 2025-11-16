<?php
class Database {
    private $host = "localhost";
    private $db_name = "ukn111534131";
    private $username = "ukn111534131";
    private $password = "ukn111534131";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);

        if ($this->conn->connect_error) {
            // Throw an exception instead of dying to allow for better error handling.
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }

        // Set UTF-8 to handle Chinese characters correctly
        $this->conn->set_charset("utf8");

        return $this->conn;
    }
}
?>