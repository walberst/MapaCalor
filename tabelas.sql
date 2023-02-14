CREATE TABLE `tb_captures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `imagem` text,
  `url` text,
  `datahora` datetime DEFAULT CURRENT_TIMESTAMP,
  `dimensao` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `tb_moviments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `ip_user` varchar(100) DEFAULT NULL,
  `geo_localization` varchar(300) DEFAULT NULL,
  `url` int DEFAULT NULL,
  `tipo_move` enum('click','move') NOT NULL,
  `x` int NOT NULL,
  `y` int NOT NULL,
  `datahora` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_captures_FK` (`url`),
  KEY `tb_captures_FK_1` (`id_user`),
  CONSTRAINT `tb_captures_FK` FOREIGN KEY (`url`) REFERENCES `tb_img_url` (`id`),
  CONSTRAINT `tb_captures_FK_1` FOREIGN KEY (`id_user`) REFERENCES `tb_usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=581 DEFAULT CHARSET=utf8mb3;