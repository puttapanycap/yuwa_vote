/*
 Navicat Premium Data Transfer

 Source Server         : [MariaDB] localhost
 Source Server Type    : MariaDB
 Source Server Version : 110502 (11.5.2-MariaDB-log)
 Source Host           : localhost:3306
 Source Schema         : yuwa-vote

 Target Server Type    : MariaDB
 Target Server Version : 110502 (11.5.2-MariaDB-log)
 File Encoding         : 65001

 Date: 13/01/2025 00:15:17
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for vote_choices
-- ----------------------------
DROP TABLE IF EXISTS `vote_choices`;
CREATE TABLE `vote_choices`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NULL DEFAULT NULL,
  `choice_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `choice_sort` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `topic_id`(`topic_id` ASC) USING BTREE,
  CONSTRAINT `vote_choices_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `vote_topics` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vote_choices
-- ----------------------------
INSERT INTO `vote_choices` VALUES (5, 3, 'การแสดงที่ 1', 1);
INSERT INTO `vote_choices` VALUES (6, 3, 'การแสดงที่ 2', 2);
INSERT INTO `vote_choices` VALUES (7, 3, 'การแสดงที่ 4', 3);
INSERT INTO `vote_choices` VALUES (8, 3, 'การแสดงที่ 3', 4);
INSERT INTO `vote_choices` VALUES (9, 4, 'ตัวเลือกที่ 1', 1);
INSERT INTO `vote_choices` VALUES (10, 4, 'ตัวเลือกที่ 2', 2);

-- ----------------------------
-- Table structure for vote_members
-- ----------------------------
DROP TABLE IF EXISTS `vote_members`;
CREATE TABLE `vote_members`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vote_members
-- ----------------------------
INSERT INTO `vote_members` VALUES (1, NULL, 'admin', NULL);

-- ----------------------------
-- Table structure for vote_results
-- ----------------------------
DROP TABLE IF EXISTS `vote_results`;
CREATE TABLE `vote_results`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NULL DEFAULT NULL,
  `choice_id` int(11) NULL DEFAULT NULL,
  `timestamp` datetime NULL DEFAULT NULL,
  `ipaddress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cookie_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `topic_id`(`topic_id` ASC) USING BTREE,
  INDEX `choice_id`(`choice_id` ASC) USING BTREE,
  CONSTRAINT `vote_results_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `vote_topics` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `vote_results_ibfk_2` FOREIGN KEY (`choice_id`) REFERENCES `vote_choices` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vote_results
-- ----------------------------

-- ----------------------------
-- Table structure for vote_topics
-- ----------------------------
DROP TABLE IF EXISTS `vote_topics`;
CREATE TABLE `vote_topics`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `expire_datetime` datetime NULL DEFAULT NULL,
  `member_id` int(11) NULL DEFAULT 1,
  `share_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vote_topics
-- ----------------------------
INSERT INTO `vote_topics` VALUES (3, 'ทดสอบเปิด Vote', '2025-01-13 00:15:00', 1, '9f8b7c4d3a2e1f5601d0c9ab3e4856');
INSERT INTO `vote_topics` VALUES (4, 'ทดสอบเปิด Vote 2', '2025-01-13 16:00:00', 1, '7d7f128df78b991f');

SET FOREIGN_KEY_CHECKS = 1;
