#SXD20|20011|50525|50313|2014.01.21 23:43:04|agromat|0|1|5|
#TA collections`5`16384
#EOH

#	TC`collections`utf8_unicode_ci	;
CREATE TABLE `collections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `brands_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci	;
#	TD`collections`utf8_unicode_ci	;
INSERT INTO `collections` VALUES 
(5,'Диетическая',3),
(9,'Стандартная',3),
(10,'2 литра',3),
(11,'Синий',4),
(12,'Зелёный',4)	;
