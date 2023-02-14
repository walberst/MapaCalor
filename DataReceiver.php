<?php
include_once "Database.php";

/**
 * Classe que recebe os dados de captura de eventos do mouse e imagens
 */
class DataReceiver
{
  /**
   * Instância da classe Database para a tabela tb_moviments
   * @var Database
   */
  private $mouse;

  /**
   * Instância da classe Database para a tabela tb_captures
   * @var Database
   */
  private $capture;

  /**
   * Construtor da classe
   */
  public function __construct()
  {
    // Inicializa as variáveis mouse e capture com instâncias da classe Database
    $this->mouse  = new Database("tb_moviments");
    $this->capture  = new Database("tb_captures");
  }

  /**
   * Função que recebe os dados da captura
   */
  public function receiveData()
  {
    // Recebe os dados em formato JSON e decodifica para um array
    $data = json_decode(file_get_contents("php://input"), true);

    // Verifica se o tipo de dados está definido
    if (!isset($data["type"])) {
      // Retorna um array vazio se o tipo não estiver definido
      echo json_encode([]);
      return;
    }

    // Verifica se a URL já existe na tabela tb_captures
    $idUrl = $this->capture->select("url = '{$data["url"]}'")->fetch(PDO::FETCH_ASSOC);

    // Se a URL não existe e o tipo de dados não é "image", retorna um array vazio
    if (!$idUrl && $data["type"] <> "image") {
      echo json_encode([]);
      return false;
    }
    // Se a URL já existe e o tipo de dados é "image", retorna um array vazio
    elseif ($idUrl && $data["type"] == "image") {
      echo json_encode([]);
      return false;
    }

    // Verifica o tipo de dados e processa-os de acordo
    switch ($data["type"]) {
      case "data":
        // Processa os dados de cliques e movimentos do mouse
        echo json_encode([
          $this->processClickData($data["clicks"], $idUrl["id"]),
          $this->processMoveData($data["movements"], $idUrl["id"])
        ]);
        break;
      case "image":
        // Processa os dados de imagem
        echo json_encode($this->processImageData($data["imageData"], $data["url"]) ?? []);
        break;
    }
  }

  /**
   * Função que processa os dados de cliques do mouse
   * @param array $clicks Dados dos cliques do mouse
   * @param int $url ID da URL na tabela tb_captures
   * @return array IDs dos cliques inseridos na tabela tb_moviments
   * 
   * @return array IDs dos cliques inseridos na tabela tb_moviments
   */
  public function processClickData($clicks, $url)
  {
    $ids = [];
    foreach ($clicks as $click) {
      $dataInsert = [
        "x" => $click["x"],
        "y" => $click["y"],
        "datahora" => $click["dateTime"],
        "geo_localization" => $click["location"],
        "url" => $url,
        "tipo_move" => "click",
        "ip_user" => $_SERVER["REMOTE_ADDR"],
        "id_user" => $_SESSION["user"]["id"] ?? null
      ];
      $ids[] = $this->mouse->insert($dataInsert);
    }
    return $ids;
  }

  /**
   * Função que processa os dados dos movimentos do mouse
   * @param array $mouseMovements Dados dos movimentos do mouse
   * @param int $url ID da URL na tabela tb_captures
   * 
   * @return array IDs dos movimentos inseridos na tabela tb_moviments
   */
  public function processMoveData($mouseMovements, $url)
  {
    $ids = [];
    foreach ($mouseMovements as $mouseMovement) {
      $dataInsert = [
        "x" => $mouseMovement["x"],
        "y" => $mouseMovement["y"],
        "datahora" => $mouseMovement["dateTime"],
        "geo_localization" => $mouseMovement["location"],
        "url" => $url,
        "tipo_move" => "move",
        "ip_user" => $_SERVER["REMOTE_ADDR"],
        "id_user" => $_SESSION["user"]["id"] ?? null
      ];
      $ids[] = $this->mouse->insert($dataInsert);
    }
    return $ids;
  }

  /**
   * Função que processa os dados da imagem capturada
   * @param string $imageData Dados da imagem codificados em base64
   * @param string $url URL da página capturada
   * 
   * @return int|null ID da imagem inserida na tabela tb_captures ou null caso a dimensão da imagem já exista
   */
  public function processImageData($imageData, $url)
  {
    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

    $imageInfo = getimagesizefromstring($image);
    $imageWidth = $imageInfo[0];
    $imageHeight = $imageInfo[1];

    $existingImage = $this->capture->select("url = '{$url}' AND dimensao = '{$imageWidth}x{$imageHeight}'")->fetch(PDO::FETCH_ASSOC);

    if (!$existingImage) {
      $fileName = "uploads/" . uniqid() . '.jpeg';

      file_put_contents($fileName, $image);

      $dataInsert = [
        "url" => $url,
        "imagem" => $fileName,
        "datahora" => date('Y-m-d H:i:s'),
        "dimensao" => "{$imageWidth}x{$imageHeight}"
      ];
      return $this->capture->insert($dataInsert);
    }
  }
}


(new DataReceiver())->receiveData();
