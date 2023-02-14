<?php
include_once 'Database.php';

$db = new Database();
$rows = $db->query("select x,y, count(id) as value from tb_moviments  group by x,y")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/heatmap.js/2.0.0/heatmap.min.js"></script>
</head>

<body>
    <div class="heatmap" style="background-image: url('uploads/63eae01088ad0.jpeg');width:1350px; height:300px"></div>

    <script>
        var heatmapInstance = h337.create({
            container: document.querySelector('.heatmap')
        });

        heatmapInstance.setData({
            max: 10,
            data: <?=json_encode($rows)?>
        });
    </script>
</body>

</html>