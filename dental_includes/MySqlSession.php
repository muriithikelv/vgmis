<?php
 interface SessionManager {
     public function _open($save_path, $session_name);
     public function _close();
     public function _read($id);
     public function _write($id, $sess_data);
     public function _destroy($id);
     public function _gc($maxlifetime);
}
/**
 * The MySqlSession class implements all methods to use a
 * database based session management instead of using text files.
 * This has the benefit that all session data can be accessed
 * at a central place. This class is supported since the PHP version 5.0.5
 * because it uses the register_shutdown_function() function to ensure that
 * all session values are stored before the PHP representation is destroyed.
 *
 * @package SessionManager
 * @subpackage MySqlSession
 * @version 1.0
 * @date 09/04/2013
 * @author Andreas Wilhelm <info@avedo.net>
 * @copyright Andreas Wilhelm
 * @link http://www.avedo.net
 */ 
class MySqlSession implements SessionManager {
    /**
     * @var pdo $pdo The PDO object used to access the database
     * @access private
     */
    private $pdo = null;
 
    /**
     * Sets the user-level session storage functions which are used
     * for storing and retrieving data associated with a session.
     *
     * @access public
     * @param pdo $pdo The PDO object used to access the database
     * @return void
     */
    public function __construct(pdo $pdo) {
        // Assign the pdo object, ...
        $this->pdo = $pdo;
 
      // ... change the ini configuration, ...
        ini_set('session.save_handler', 'user');
     
        // ... set the session handler to the class methods ...
        session_set_save_handler(
            array(&$this, '_open'),
            array(&$this, '_close'),
            array(&$this, '_read'),
            array(&$this, '_write'),
            array(&$this, '_destroy'),
            array(&$this, '_gc')
        );
         
        // ... and start a new session.
        session_start();
 
        // Finally ensure that the session values are stored.
        register_shutdown_function('session_write_close');
    }
     
    /**
     * Is called to open a session. The method
     * does nothing because we do not want to write
     * into a file so we don't need to open one.
     *
     * @access public
     * @param String $save_path The save path
     * @param String $session_name The name of the session
     * @return Boolean
     */
    public function _open($save_path, $session_name) {
        return true;
    }
     
    /**
     * Is called when the reading in a session is
     * completed. The method calls the garbage collector.
     *
     * @access public
     * @return Boolean
     */
    public function _close() {
        $this->_gc(100);
        return true;
    }
     
    /**
     * Is called to read data from a session.
     *
     * @access public
     * @access Integer $id The id of the current session
     * @return Mixed
     */
    public function _read($id) {
        // Create a query to get the session data, ...
        $select = "SELECT
                *
            FROM
                `sessions`
            WHERE
                `sessions`.`id` = :id
            LIMIT 1;";
          
      // ... prepare the statement, ...
      $selectStmt = $pdo->prepare($select);
       
      // ... bind the id parameter to the statement ...
      $selectStmt->bindParam(':id', $id, PDO::PARAM_INT);
          
      // ... and try to execute the query.
      if($selectStmt->execute()) {
         // Fetch the result as associative array ...
         $result = $selectStmt->fetch(PDO::FETCH_ASSOC);
          
         // ... and validate it.
         if( !$result ) {
            throw new Exception("MySqlSession: MySQL error while performing query.");
         }
          
         return $result["value"];
        }
         
        return '';
    }
     
    /**
     * Writes data into a session rather
     * into the session record in the database.
     *
     * @access public
     * @access Integer $id The id of the current session
     * @access String $sess_data The data of the session
     * @return Boolean
     */
    public function _write($id, $sess_data) {
        // Validate the given data.
        if( $sess_data == null ) {
            return true;
        }
     
        // Setup the query to update a session, ...
        $update = "UPDATE
                `sessions`
            SET
                `sessions`.`last_updated` = :time,
                `sessions`.`value` = :data
            WHERE
                `sessions`.`id` = :id;";
          
      // ... prepare the statement, ...
      $updateStmt = $pdo->prepare($update);
       
      // ... bind the parameters to the statement ...
      $updateStmt->bindParam(':time', time(), PDO::PARAM_INT);
      $updateStmt->bindParam(':data', $sess_data, PDO::PARAM_STR);
      $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
          
      // ... and try to execute the query.
      if($updateStmt->execute()) {
         // Check if any data set was updated.
         if($updateStmt->rowCount() > 0) {
            return true;
         } else {
            // The session does not exists create a new one, ...
            $insert = "INSERT INTO
                  `sessions`
                  (id, last_updated, start, value)
               VALUES
                  (:id, :time, :time, :data);";
          
            // ... prepare the statement, ...
            $insertStmt = $pdo->prepare($insert);
             
            // ... bind the parameters to the statement ...
            $insertStmt->bindParam(':time', time(), PDO::PARAM_INT);
            $insertStmt->bindParam(':data', $sess_data, PDO::PARAM_STR);
            $insertStmt->bindParam(':id', $id, PDO::PARAM_INT);
             
            // .. and finally execute it.
            return $insertStmt->execute();
         }
      }
       
      return false;
    }
     
    /**
     * Ends a session and deletes it.
     *
     * @access public
     * @access Integer $id The id of the current session
     * @return Boolean
     */
    public function _destroy($id) {
        // Setup a query to delete the current session, ...
        $delete = "DELETE FROM
                `sessions`
            WHERE
                `sessions`.`id` = '" . $id . "';";
          
      // ... prepare the statement, ...
      $deleteStmt = $pdo->prepare($delete);
       
      // ... bind the parameters to the statement ...
      $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
          
      // ... and execute the query.
      return $deleteStmt->execute();
    }
     
    /**
     * The garbage collector deletes all sessions from the database
     * that where not deleted by the session_destroy function.
     * so your session table will stay clean.
     *
     * @access public
     * @access Integer $maxlifetime The maximum session lifetime
     * @return Boolean
     */
    public function _gc($maxlifetime) {
        // Set a period after that a session pass off.
        $maxlifetime = strtotime("-20 minutes");
         
        // Setup a query to delete discontinued sessions, ...
        $delete = "DELETE FROM
                `sessions`
            WHERE
                `sessions`.`last_updated` < :maxlifetime;";
          
      // ... prepare the statement, ...
      $deleteStmt = $pdo->prepare($delete);
       
      // ... bind the parameters to the statement ...
      $deleteStmt->bindParam(':maxlifetime', $maxlifetime, PDO::PARAM_INT);
          
      // ... and execute the query.
      return $deleteStmt->execute();
    }
}
?>