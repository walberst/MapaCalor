<?php
include_once "Database.php";
class DataReceiver
{
  private $mouse;
  private $capture;
  public function __construct()
  {
    $this->mouse  = new Database("tb_moviments");
    $this->capture  = new Database("tb_captures");
  }

  public function receiveData()
  {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["type"])) {
      echo json_encode([]);
      return;
    }

    $idUrl = $this->capture->select("url = '{$data["url"]}'")->fetch(PDO::FETCH_ASSOC);
    if (!$idUrl && $data["type"] <> "image") {
      echo json_encode([]);
      return false;
    } elseif ($idUrl && $data["type"] == "image") {
      echo json_encode([]);
      return false;
    }

    switch ($data["type"]) {
      case "data":
        echo json_encode([
          $this->processClickData($data["clicks"], $idUrl["id"]),
          $this->processMoveData($data["movements"], $idUrl["id"])
        ]);
        break;
      case "image":
        echo json_encode($this->processImageData($data["imageData"], $data["url"])??[]);
        break;
    }
  }

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
        "id_user" => $_SESSION["user"]["id"]??null
      ];
      $ids[] = $this->mouse->insert($dataInsert);
    }
    return $ids;
  }

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
        "id_user" => $_SESSION["user"]["id"]??null
      ];
      $ids[] = $this->mouse->insert($dataInsert);
    }
    return $ids;
  }

  public function processImageData($imageData, $url)
  {
    // decode the base64 encoded image data
    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

    // get the image dimensions
    $imageInfo = getimagesizefromstring($image);
    $imageWidth = $imageInfo[0];
    $imageHeight = $imageInfo[1];

    // check if the image dimension already exists in the database
    $existingImage = $this->capture->select("url = '{$url}' AND dimensao = '{$imageWidth}x{$imageHeight}'")->fetch(PDO::FETCH_ASSOC);

    // if the image dimension doesn't exist, insert it into the database
    if (!$existingImage) {
      // generate a unique file name for the image
      $fileName = "uploads/" . uniqid() . '.jpeg';

      // store the image on the server
      file_put_contents($fileName, $image);

      // insert the image information into the database
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