<?php


/**
 * Class Database
 *
 * A classe representa uma conexão com o banco de dados MySQL.
 */
class Database
{
    /**
     * Variáveis privadas para armazenar as informações de conexão
     */
    private $HOST;
    private $DBNAME;
    private $USER;
    private $PASS;
    private $PORT;
    private $table;
    private $connection;
    private bool $debug;

    /**
     * Construtor da classe Database
     * 
     * @param string $table O nome da tabela a ser usada.
     * @param bool $debug Modo de depuração.
     */
    public function __construct($table = null, $debug = false)
    {
        $this->HOST = "localhost";
        $this->DBNAME = "testes";
        $this->USER = "root";
        $this->PASS = "";
        $this->PORT = "3306";
        $this->table = $table;
        $this->setConnection();
        $this->debug = $debug;
    }

    /**
     * Método privado para configurar a conexão com o banco de dados
     */
    private function setConnection()
    {
        try {
            $opcoes = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
            );
            $this->connection = new PDO('mysql:host=' . $this->HOST . ';port=' . $this->PORT . ';dbname=' . $this->DBNAME, $this->USER, $this->PASS, $opcoes);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('ERROR: ' . $e->getMessage());
        }
    }

    /**
     * Método público para executar uma consulta no banco de dados
     *
     * @param string $query A consulta SQL a ser executada.
     * @param array $params Os parâmetros da consulta.
     *
     * @return PDOStatement O resultado da consulta.
     */
    public function execute($query, $params = [])
    {
        try {
            if ($this->debug) {
                echo "<pre>";
                var_dump($query, $params);
                echo "</pre>";
            }
            $statement = $this->connection->prepare($query);
            $statement->execute($params);
            return $statement;
        } catch (PDOException $e) {
            die('ERROR: ' . $e->getMessage());
        }
    }

    /**
     * Insere um novo registro na tabela
     *
     * @param array $values Valores a serem inseridos
     * @return int ID do último registro inserido
     */
    public function insert(array $values)
    {
        $fields = array_keys($values);
        $binds  = array_pad([], count($fields), '?');
        $query = 'INSERT INTO ' . $this->table . ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $binds) . ')';
        $this->execute($query, array_values($values));
        return $this->connection->lastInsertId();
    }


    /**
     * Realiza uma consulta SELECT na tabela especificada.
     *
     * @param string|null $where Cláusula WHERE opcional da consulta SELECT.
     * @param string|null $order Cláusula ORDER BY opcional da consulta SELECT.
     * @param string|null $limit Cláusula LIMIT opcional da consulta SELECT.
     * @param string      $fields Campos que serão retornados na consulta. Padrão é '*'.
     * @param bool        $idIndice Se verdadeiro, os resultados serão indexados pelo ID.
     *
     * @return PDOStatement Resultado da consulta SELECT.
     */
    public function select($where = null, $order = null, $limit = null, $fields = '*', $idIndice = false)
    {
        // Monta a cláusula WHERE, se existir.
        $where = $where != '' ? 'WHERE ' . $where : '';
        // Monta a cláusula ORDER BY, se existir.
        $order = $order != '' ? 'ORDER BY ' . $order : '';
        // Monta a cláusula LIMIT, se existir.
        $limit = $limit != '' ? 'LIMIT ' . $limit : '';
        // Monta a consulta SELECT completa.
        $query = 'SELECT ' . $fields . ' FROM ' . $this->table . ' ' . $where . ' ' . $order . ' ' . $limit;
        // Executa a consulta SELECT.
        $stat = $this->execute($query);
        // Verifica se é necessário indexar os resultados pelo ID.
        if ($idIndice) {
            $resultado = $stat->fetchAll(PDO::FETCH_ASSOC);
            // Verifica se há um campo 'id' nos resultados.
            if (isset($resultado[0]["id"])) {
                $result = [];
                // Indexa os resultados pelo ID.
                foreach ($resultado as $res) {
                    $result[$res["id"]] = $res;
                }
                // Retorna os resultados indexados.
                return $result;
            }
            // Retorna os resultados sem indexação.
            return $stat;
        }
        // Retorna os resultados sem indexação.
        return $stat;
    }

    /**
     * Executa uma consulta SQL e retorna o resultado.
     * Apenas consultas do tipo SELECT são permitidas.
     *
     * @param string $sql A consulta SQL a ser executada.
     *
     * @return array|null O resultado da consulta em forma de array associativo, ou null se a consulta não for do tipo SELECT.
     */
    public function query(string $sql)
    {
        if (stripos($sql, 'SELECT') !== 0) {
            return null;
        }

        $statement = $this->execute($sql);
        return $statement;
    }

    /**
     * Método para atualizar registros na tabela.
     * 
     * @param string $where Condição para identificar os registros a serem atualizados.
     * @param array $values Valores a serem atualizados, onde a chave é o nome da coluna e o valor é o novo valor.
     * 
     * @return bool Retorna true se a atualização foi bem sucedida.
     */
    public function update(string $where, array $values)
    {
        $fields = array_keys($values);
        $query = 'UPDATE ' . $this->table . ' SET ' . implode('=?,', $fields) . '=? WHERE ' . $where;
        $this->execute($query, array_values($values));
        return true;
    }

    /**
     * Método para excluir registros na tabela.
     * 
     * @param string $where Condição para identificar os registros a serem excluídos.
     * 
     * @return bool Retorna true se a exclusão foi bem sucedida.
     */
    public function delete($where)
    {
        $query = 'DELETE FROM ' . $this->table . ' WHERE ' . $where;
        $this->execute($query);
        return true;
    }
}
