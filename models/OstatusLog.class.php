<?php
/*
 * Copyright (c) 2013 Rasmus Fuhse <fuhse@data-quest.de>
 * 
 * MIT license (http://opensource.org/licenses/MIT)
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy 
 * of this software and associated documentation files (the "Software"), to deal 
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is 
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
 * THE SOFTWARE.
 */

class OstatusLog {
    
    static protected $instance = null;
    
    protected $destination = null;
    protected $db = null;
    
    static public function log($description, $user_id = null, $contact_id = null, $data = null) {
        return self::get()->addEntry($description, $user_id, $contact_id, $data);
    }
    
    static public function get() {
        if (self::$instance !== null) {
            return self::$instance;
        }
        $class = get_called_class();
        //You can set the variable $GLOBALS['OSTATUS_LOG_FILE'] in the 
        //local_config.inc.php if the /tmp folder isn't okay for you.
        $destination = $GLOBALS['OSTATUS_LOG_FILE']
                        ? $GLOBALS['OSTATUS_LOG_FILE']
                        : $GLOBALS['TMP_PATH']."/ostatuslog_".$GLOBALS['STUDIP_INSTALLATION_ID'].".sqlite";
        $log = new $class($destination);
        return self::$instance = $log;
    }
    
    public function __construct($destination) {
        $this->destination = $destination;
        $this->db = new SQLite3($this->destination);
        $this->checkDB();
    }
    
    public function entries($since = null, $limit = null, $user_id = null, $contact_id = null, $description = null) {
        $statement = $this->db->query(
            "SELECT * " .
            "FROM ostatuslog " .
            "ORDER BY mkdate DESC " .
        "");
        $results = array();
        while ($result = $statement->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $result;
        }
        return $results;
    }
    
    public function addEntry($description, $user_id = null, $contact_id = null, $data = null) {
        $statement = $this->db->prepare(
            "INSERT INTO ostatuslog (description, user_id, contact_id, data, mkdate) " .
            "VALUES (:description, :user_id, :contact_id, :data, strftime('%s','now')) " .
        "");
        $statement->bindValue('description', $description);
        $statement->bindValue('user_id', $user_id);
        $statement->bindValue('contact_id', $contact_id);
        $statement->bindValue('data', is_array($data) || is_object($data) ? json_encode($data) : $data);
        return $statement->execute() !== false;
    }
    
    /**
     * Inits the sqlite database with tables and a first log-entry (log has started)
     */
    protected function checkDB() {
        $result = $this->db->query(
            "SELECT * FROM sqlite_master WHERE type='table' " .
        "");
        $ostatuslog_table_exists = false;
        while ($table = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($table['name'] === "ostatuslog") {
                $ostatuslog_table_exists = true;
            }
        }
        if (!$ostatuslog_table_exists) {
            $this->db->exec("
                CREATE TABLE ostatuslog (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id VAR_CHAR(32) NULL,
                    contact_id VAR_CHAR(32) NULL,
                    description VAR_CHAR(64) NOT NULL,
                    data TEXT NULL,
                    mkdate INTEGER NOT NULL
                ) 
            ");
            $this->addEntry("Logging database initialized!");
        }
    }
    
}