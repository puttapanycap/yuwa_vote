/*
 Navicat Premium Dump SQL

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80030 (8.0.30)
 Source Host           : localhost:3306
 Source Schema         : vote

 Target Server Type    : MySQL
 Target Server Version : 80030 (8.0.30)
 File Encoding         : 65001

 Date: 03/02/2026 14:36:26
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for vote_choices
-- ----------------------------
DROP TABLE IF EXISTS `vote_choices`;
CREATE TABLE `vote_choices`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `topic_id` int NULL DEFAULT NULL,
  `choice_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `choice_sort` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `topic_id`(`topic_id` ASC) USING BTREE,
  CONSTRAINT `vote_choices_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `vote_topics` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of vote_choices
-- ----------------------------

-- ----------------------------
-- Table structure for vote_members
-- ----------------------------
DROP TABLE IF EXISTS `vote_members`;
CREATE TABLE `vote_members`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `member_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `member_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of vote_members
-- ----------------------------
INSERT INTO `vote_members` VALUES (1, 'YUWA IT', 'admin', '$2y$10$oaoOGbk90tOdon59Nmirku4xjqz/oqNQFZRc4NMkXWcSsIL5VovC2', 'admin@example.com', '2025-01-14 16:00:00');

-- ----------------------------
-- Table structure for vote_results
-- ----------------------------
DROP TABLE IF EXISTS `vote_results`;
CREATE TABLE `vote_results`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `topic_id` int NULL DEFAULT NULL,
  `choice_id` int NULL DEFAULT NULL,
  `timestamp` datetime NULL DEFAULT NULL,
  `ipaddress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cookie_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `topic_id`(`topic_id` ASC) USING BTREE,
  INDEX `choice_id`(`choice_id` ASC) USING BTREE,
  CONSTRAINT `vote_results_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `vote_topics` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `vote_results_ibfk_2` FOREIGN KEY (`choice_id`) REFERENCES `vote_choices` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of vote_results
-- ----------------------------

-- ----------------------------
-- Table structure for vote_topics
-- ----------------------------
DROP TABLE IF EXISTS `vote_topics`;
CREATE TABLE `vote_topics`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `topic_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `expire_datetime` datetime NULL DEFAULT NULL,
  `member_id` int NULL DEFAULT 1,
  `share_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `session_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `show_score` tinyint(1) NULL DEFAULT 1,
  `display_mode` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'card',
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether the topic monitor page is publicly accessible (1=public, 0=private)',
  `vote_mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'single',
  `max_choices` int NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_session_key`(`session_key` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of vote_topics
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
